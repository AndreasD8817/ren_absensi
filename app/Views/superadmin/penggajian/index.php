<?php
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$sudah_generate = !empty($data_gaji);
$total_gaji_bersih_semua = array_sum(array_column($data_gaji, 'total_gaji_bersih'));
$total_potongan_semua    = array_sum(array_column($data_gaji, 'total_potongan_telat_alfa'));
$semua_published         = $sudah_generate && !in_array('draft', array_column($data_gaji, 'status'));

// Hitung jumlah pegawai per cabang untuk filter ringkasan
$cabang_counts = [];
foreach ($ringkasan as $r) {
    $c_id = $r['id_cabang'];
    $cabang_counts[$c_id] = ($cabang_counts[$c_id] ?? 0) + 1;
}
?>
<!-- Halaman Penggajian - Superadmin -->
<div class="flex h-screen overflow-hidden bg-gray-50">

    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header + Filter -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Penggajian Bulanan</h2>
                <p class="text-gray-500 text-sm mt-1">Kalkulasi otomatis gaji bersih seluruh pegawai</p>
            </div>
            <!-- Filter Bulan & Tahun -->
            <form method="GET" action="<?= BASE_URL ?>/superadmin/penggajian" class="flex items-center gap-3 bg-white px-4 py-3 rounded-2xl shadow-sm border border-gray-100">
                <select name="bulan" class="text-sm border-0 outline-none focus:ring-0 font-semibold text-gray-700 bg-transparent">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>><?= $nama_bulan[$i] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="tahun" class="text-sm border-0 outline-none focus:ring-0 font-semibold text-gray-700 bg-transparent">
                    <?php for ($y = date('Y'); $y >= date('Y')-3; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-blue-800 transition-colors">Tampilkan</button>
            </form>
        </div>

        <!-- Kartu Ringkasan -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                <p class="text-xs font-semibold text-gray-500 mb-1">Total Pegawai</p>
                <h3 id="ringkasan_pegawai" class="text-3xl font-bold text-gray-800"><?= count($ringkasan) ?></h3>
                <p class="text-xs text-blue-500 mt-1">Aktif di periode ini</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 border-l-4 border-l-red-400">
                <p class="text-xs font-semibold text-gray-500 mb-1">Total Potongan</p>
                <h3 id="ringkasan_potongan" class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_potongan_semua, 0, ',', '.') ?></h3>
                <p class="text-xs text-red-500 mt-1">Denda terlambat + alfa</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 border-l-4 border-l-green-500">
                <p class="text-xs font-semibold text-gray-500 mb-1">Total Gaji Bersih</p>
                <h3 id="ringkasan_gaji_bersih" class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_gaji_bersih_semua, 0, ',', '.') ?></h3>
                <p class="text-xs text-green-500 mt-1">Semua pegawai</p>
            </div>
        </div>

        <!-- Tombol Aksi Generate / Publish -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div class="flex flex-wrap items-center gap-2">
                <?php if ($sudah_generate): ?>
                <a href="<?= BASE_URL ?>/superadmin/export_gaji?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&format=excel" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 text-sm font-bold rounded-xl hover:bg-green-200 transition-colors">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <a href="<?= BASE_URL ?>/superadmin/export_gaji?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&format=pdf" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 text-sm font-bold rounded-xl hover:bg-red-200 transition-colors mr-2">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
                <?php endif; ?>

                <!-- DROPDOWN CABANG UNTUK GENERATE -->
                <select id="generate_cabang" onchange="filterGaji()" class="text-sm px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-gray-700 font-semibold shadow-sm">
                    <option value="all" selected>Semua Cabang (Lama)</option>
                    <?php foreach ($list_cabang as $c): ?>
                    <option value="<?= $c['id_cabang'] ?>"><?= esc($c['nama_cabang']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex gap-3">
                <?php if (!$sudah_generate): ?>
                <button onclick="generateGaji()" class="flex items-center gap-2 px-6 py-2.5 bg-yellow-500 text-white text-sm font-semibold rounded-xl shadow-md hover:bg-yellow-600 active:scale-95 transition-all">
                    <i class="fa-solid fa-calculator"></i> Generate Gaji <?= $nama_bulan[$bulan] ?> <?= $tahun ?>
                </button>
                <?php elseif (!$semua_published): ?>
                <button onclick="generateGaji()" class="flex items-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-all">
                    <i class="fa-solid fa-rotate"></i> Re-generate
                </button>
            <button onclick="publishGaji()" class="flex items-center gap-2 px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl shadow-md hover:bg-green-700 active:scale-95 transition-all">
                <i class="fa-solid fa-paper-plane"></i> Publish Gaji ke Pegawai
            </button>
            <?php else: ?>
            <span class="flex items-center gap-2 px-5 py-2.5 bg-green-100 text-green-700 text-sm font-bold rounded-xl">
                <i class="fa-solid fa-circle-check"></i> Gaji Sudah Dipublikasikan
            </span>
            <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Data Gaji -->
        <?php if ($sudah_generate): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-700">Rincian Gaji — <?= $nama_bulan[$bulan] ?> <?= $tahun ?></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Pegawai</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Cabang</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Gaji Pokok</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Terlambat</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Potongan BPJS</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Gaji Bersih</th>
                            <th class="text-center px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Slip Gaji</th>
                            <th class="text-center px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($data_gaji as $g): ?>
                        <?php $total_potongan_bpjs_pajak = $g['pot_jht_2'] + $g['pot_bpjs_kes_1'] + $g['pot_jp_1'] + $g['pot_pph21']; ?>
                        <tr class="row-gaji hover:bg-gray-50 transition-colors"
                            data-cabang="<?= $g['id_cabang'] ?>"
                            data-gapok="<?= $g['nilai_gaji_pokok'] ?>"
                            data-potongan="<?= $g['total_potongan_telat_alfa'] ?>"
                            data-bpjs="<?= $total_potongan_bpjs_pajak ?>"
                            data-bersih="<?= $g['total_gaji_bersih'] ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($g['nama_lengkap']) ?>&size=36&background=random" class="w-9 h-9 rounded-full" alt="">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= esc($g['nama_lengkap']) ?></p>
                                        <p class="text-gray-400 text-xs"><?= esc($g['nip']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($g['nama_cabang']) ?></td>
                            <td class="px-6 py-4 text-right text-gray-700">Rp <?= number_format($g['nilai_gaji_pokok'],0,',','.') ?></td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($g['total_potongan_telat_alfa'] > 0): ?>
                                    <button onclick="bukaDenda(<?= $g['id_user'] ?>, '<?= esc($g['nama_lengkap']) ?>', <?= $bulan ?>, <?= $tahun ?>)" class="text-red-500 font-semibold hover:text-red-700 underline decoration-red-200 underline-offset-4 transition-colors">
                                        - Rp <?= number_format($g['total_potongan_telat_alfa'],0,',','.') ?>
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400">- Rp 0</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500">- Rp <?= number_format($total_potongan_bpjs_pajak,0,',','.') ?></td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800">Rp <?= number_format($g['total_gaji_bersih'],0,',','.') ?></td>
                            <td class="px-6 py-4 text-center">
                                <button onclick='bukaSlipGaji(<?= json_encode($g) ?>)' class="px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg hover:bg-blue-600 hover:text-white transition-colors">
                                    <i class="fa-solid fa-eye"></i> Lihat Slip
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($g['status'] === 'published'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Published</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Draft</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="2" class="px-6 py-4 font-bold text-gray-700">TOTAL</td>
                            <td id="total-footer-gapok" class="px-6 py-4 text-right font-bold text-gray-700">Rp <?= number_format(array_sum(array_column($data_gaji,'nilai_gaji_pokok')),0,',','.') ?></td>
                            <td id="total-footer-potongan" class="px-6 py-4 text-right font-bold text-red-500">- Rp <?= number_format($total_potongan_semua,0,',','.') ?></td>
                            <?php 
                            $total_bpjs_semua = array_sum(array_column($data_gaji, 'pot_jht_2')) + array_sum(array_column($data_gaji, 'pot_bpjs_kes_1')) + array_sum(array_column($data_gaji, 'pot_jp_1')) + array_sum(array_column($data_gaji, 'pot_pph21')); 
                            ?>
                            <td id="total-footer-bpjs" class="px-6 py-4 text-right font-bold text-gray-500">- Rp <?= number_format($total_bpjs_semua,0,',','.') ?></td>
                            <td id="total-footer-bersih" class="px-6 py-4 text-right font-bold text-green-700 text-base">Rp <?= number_format($total_gaji_bersih_semua,0,',','.') ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php else: ?>
        <!-- State kosong -->
        <div class="bg-white rounded-2xl p-16 text-center border border-dashed border-gray-200">
            <i class="fa-solid fa-calculator text-5xl text-gray-200 mb-4"></i>
            <h3 class="text-lg font-bold text-gray-500">Gaji Belum Di-Generate</h3>
            <p class="text-gray-400 text-sm mt-2">Klik tombol <b>"Generate Gaji"</b> di atas untuk mulai menghitung gaji bersih seluruh pegawai di bulan ini.</p>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- ==================== MODAL RINCIAN DENDA TELAT ==================== -->
<div id="modal-denda" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto pt-12 pb-12">
    <div class="bg-white rounded-sm shadow-2xl w-full max-w-2xl font-mono text-sm relative mt-4">
        
        <div class="absolute top-4 right-4 flex gap-2 z-40 print:hidden">
            <button onclick="cetakDenda()" class="px-4 py-2 bg-blue-600 text-white rounded shadow-sm hover:bg-blue-700 transition-colors text-xs font-sans">
                <i class="fa-solid fa-print mr-2"></i>Cetak PDF
            </button>
            <button onclick="tutupDenda()" class="px-4 py-2 bg-red-100 text-red-600 rounded shadow-sm hover:bg-red-500 hover:text-white transition-colors text-xs font-sans">
                <i class="fa-solid fa-xmark mr-2"></i>Tutup
            </button>
        </div>
        
        <div id="area-cetak-denda" class="p-10 bg-white text-black w-[794px] max-w-[794px] mx-auto overflow-hidden print:w-auto print:overflow-visible">
            <div class="mb-4">
                <h1 class="font-bold text-lg leading-tight uppercase">PT REN</h1>
                <p class="text-xs">Rincian Potongan Keterlambatan</p>
            </div>
            
            <h2 class="text-center font-bold text-lg mb-6 tracking-widest border-b-2 border-black pb-4">PENALTY SHEET</h2>
            
            <div class="grid grid-cols-2 gap-x-12 gap-y-1 mb-6 text-xs">
                <div class="flex"><div class="w-24">Nama</div><div>: <span id="denda-nama"></span></div></div>
                <div class="flex"><div class="w-32">Periode</div><div>: <span id="denda-periode"></span></div></div>
            </div>
            
            <table class="w-full text-xs text-left mb-6">
                <thead>
                    <tr class="border-y-2 border-black font-bold">
                        <th class="py-2 w-10">No</th>
                        <th class="py-2">Tanggal</th>
                        <th class="py-2 text-center">Jam Masuk</th>
                        <th class="py-2 text-center">Menit Telat / Jenis Denda</th>
                        <th class="py-2 text-right">Potongan</th>
                    </tr>
                </thead>
                <tbody id="tbody-denda">
                    <tr><td colspan="5" class="py-4 text-center">Memuat...</td></tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-black font-bold text-sm">
                        <td colspan="4" class="py-3 text-right">TOTAL POTONGAN:</td>
                        <td class="py-3 text-right text-red-600" id="denda-total"></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="border-t-2 border-black mt-8 pt-2">
                <p class="text-[10px] italic">* Rincian di atas merupakan akumulasi denda yang dipotongkan pada gaji periode ini sesuai ketentuan cabang.</p>
            </div>
            
        </div>
    </div>
</div>

<!-- ==================== MODAL SLIP GAJI ==================== -->
<div id="modal-slip" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto pt-12 pb-12">
    <div class="bg-white rounded-sm shadow-2xl w-full max-w-4xl font-mono text-sm relative mt-4">
        
        <!-- Action Buttons (Print & Close) -->
        <div class="absolute top-4 right-4 flex gap-2 z-40 print:hidden">
            <button onclick="cetakSlip()" class="px-4 py-2 bg-blue-600 text-white rounded shadow-sm hover:bg-blue-700 transition-colors text-xs font-sans">
                <i class="fa-solid fa-print mr-2"></i>Cetak PDF
            </button>
            <button onclick="tutupSlip()" class="px-4 py-2 bg-red-100 text-red-600 rounded shadow-sm hover:bg-red-500 hover:text-white transition-colors text-xs font-sans">
                <i class="fa-solid fa-xmark mr-2"></i>Tutup
            </button>
        </div>
        
        <div id="area-cetak-slip" class="p-10 bg-white text-black w-[794px] max-w-[794px] mx-auto overflow-hidden print:w-auto print:overflow-visible">
            <!-- Header Perusahaan -->
            <div class="mb-4">
                <h1 class="font-bold text-lg leading-tight uppercase">PT REN</h1>
                <p class="text-xs">Kantor Pusat PT REN</p>
                <p class="text-xs">Indonesia</p>
            </div>
            
            <h2 class="text-center font-bold text-lg mb-6 tracking-widest border-b-2 border-black pb-4">PAYROLL SHEET</h2>
            
            <!-- Identitas Pegawai -->
            <div class="grid grid-cols-2 gap-x-12 gap-y-1 mb-8 text-xs">
                <div class="flex"><div class="w-24">NIK</div><div>: <span id="slip-nip"></span></div></div>
                <div class="flex"><div class="w-32">Periode</div><div>: <span id="slip-periode"></span></div></div>
                <div class="flex"><div class="w-24">Nama</div><div>: <span id="slip-nama"></span></div></div>
                <div class="flex"><div class="w-32">Status Pajak</div><div>: <span id="slip-pajak"></span></div></div>
                <div class="flex"><div class="w-24">Jabatan</div><div>: <span id="slip-jabatan"></span></div></div>
                <div class="flex"><div class="w-32">Cabang</div><div>: <span id="slip-cabang"></span></div></div>
            </div>
            
            <div class="border-t-2 border-black mb-4"></div>
            
            <!-- Dua Kolom Pendapatan & Pengurangan -->
            <div class="grid grid-cols-2 gap-8 text-xs">
                <!-- Kolom Kiri: PENDAPATAN -->
                <div>
                    <h3 class="font-bold mb-4 uppercase">PENDAPATAN :</h3>
                    
                    <div class="flex justify-between mb-1"><div class="flex-1">- Gaji Pokok</div><div class="w-4">:</div><div class="w-24 text-right" id="p-gaji"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Bonus / THR</div><div class="w-4">:</div><div class="w-24 text-right" id="p-bonus"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Tunjangan Jabatan</div><div class="w-4">:</div><div class="w-24 text-right" id="p-tjab"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Tunjangan* Transportasi</div><div class="w-4">:</div><div class="w-24 text-right" id="p-ttrans"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1 pl-16">Makan</div><div class="w-4">:</div><div class="w-24 text-right" id="p-tmakan"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1 pl-16">Kehadiran</div><div class="w-4">:</div><div class="w-24 text-right" id="p-thadir"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Overtime</div><div class="w-4">:</div><div class="w-24 text-right" id="p-overtime">0</div></div>
                    <div class="flex justify-between mb-2"><div class="flex-1">- Lainnya</div><div class="w-4">:</div><div class="w-24 text-right" id="p-tlain"></div></div>
                    
                    <div class="flex justify-between font-bold border-t border-black pt-1 mb-6">
                        <div class="flex-1">Gaji & Tunjangan</div><div class="w-24 text-right" id="p-subtotal1"></div>
                    </div>
                    
                    <div class="flex justify-between mb-1"><div class="flex-1">- JHT (3.70%)</div><div class="w-4">:</div><div class="w-24 text-right" id="p-jht37"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JKK (0.24%)</div><div class="w-4">:</div><div class="w-24 text-right" id="p-jkk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JK (0.30%)</div><div class="w-4">:</div><div class="w-24 text-right" id="p-jk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- BPJS Kesehatan (4%)</div><div class="w-4">:</div><div class="w-24 text-right" id="p-bpjs4"></div></div>
                    <div class="flex justify-between mb-2"><div class="flex-1">- Jaminan Pensiun (2%)</div><div class="w-4">:</div><div class="w-24 text-right" id="p-jp2"></div></div>
                    
                    <div class="border-t-2 border-black mb-1"></div>
                    <div class="flex justify-between mb-4 font-bold"><div class="w-full text-right" id="p-subtotal2"></div></div>
                    
                    <div class="flex justify-between font-bold mb-4"><div class="w-32">Total Gaji</div><div class="w-4">:</div><div class="flex-1 text-right" id="total-pendapatan"></div></div>
                    <div class="flex justify-between font-bold mb-2"><div class="w-32">Total Diterima</div><div class="w-4">:</div><div class="flex-1 text-right text-base" id="total-diterima"></div></div>
                </div>
                
                <!-- Kolom Kanan: PENGURANGAN -->
                <div>
                    <h3 class="font-bold mb-4 uppercase">PENGURANGAN :</h3>
                    
                    <div class="flex justify-between mb-1"><div class="flex-1">- Pinjaman Karyawan</div><div class="w-4">:</div><div class="w-24 text-right">0</div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Pajak Penghasilan (PPh 21)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-pph21"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JHT (2%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-jht2"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JHT (3.70%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-jht37"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JKK (0.24%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-jkk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JK (0.30%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-jk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- BPJS Kesehatan (1%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-bpjs1"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- BPJS Kesehatan (4%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-bpjs4"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Jaminan Pensiun (1%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-jp1"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Jaminan Pensiun (2%)</div><div class="w-4">:</div><div class="w-24 text-right" id="m-jp2"></div></div>
                    <div class="flex justify-between mb-2"><div class="flex-1">- Lainnya</div><div class="w-4">:</div><div class="w-24 text-right">0</div></div>
                    
                    <div class="border-t-2 border-black pt-2 mt-4 flex justify-between font-bold">
                        <div class="flex-1">Total Pengurangan</div><div class="w-4">:</div><div class="w-24 text-right" id="total-pengurangan"></div>
                    </div>
                </div>
            </div>
            
            <div class="border-t-2 border-black mt-8 pt-2">
                <p class="text-[10px] italic">* Diberikan utuh jika tanpa absensi dan ijin, mengikuti ketentuan perusahaan</p>
            </div>
            
            <div class="mt-16 pb-8">
                <p class="text-xs mb-16">Diterima oleh :</p>
                <p class="text-xs font-bold underline" id="slip-ttd"></p>
            </div>
            
        </div>
    </div>
</div>

<script>
    const formatter = new Intl.NumberFormat('id-ID');
    const namaBulan = <?= json_encode($nama_bulan) ?>;

    async function bukaDenda(id_user, nama, bulan, tahun) {
        document.getElementById('modal-denda').classList.remove('hidden');
        document.getElementById('modal-denda').classList.add('flex');
        
        document.getElementById('denda-nama').innerText = nama;
        document.getElementById('denda-periode').innerText = namaBulan[bulan] + ' ' + tahun;
        document.getElementById('tbody-denda').innerHTML = '<tr><td colspan="5" class="py-4 text-center">Memuat data...</td></tr>';
        document.getElementById('denda-total').innerText = 'Rp 0';
        
        const resp = await fetch(`<?= BASE_URL ?>/superadmin/detail_denda_telat?id_user=${id_user}&bulan=${bulan}&tahun=${tahun}`);
        const json = await resp.json();
        
        if (json.status === 'success') {
            let html = '';
            if (json.data.length === 0) {
                html = '<tr><td colspan="5" class="py-4 text-center">Tidak ada denda telat.</td></tr>';
            } else {
                json.data.forEach((d, i) => {
                    const tgl = d.is_alfa_summary ? '-' : new Date(d.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                    const jamMasuk = d.jam_masuk || '-';
                    const jenis = d.jenis_denda || (d.menit_terlambat + ' m');
                    
                    html += `<tr>
                        <td class="py-2 border-b border-gray-200">${i+1}</td>
                        <td class="py-2 border-b border-gray-200">${tgl}</td>
                        <td class="py-2 border-b border-gray-200 text-center">${jamMasuk}</td>
                        <td class="py-2 border-b border-gray-200 text-center text-red-500 font-bold">${jenis}</td>
                        <td class="py-2 border-b border-gray-200 text-right">- Rp ${formatter.format(d.denda)}</td>
                    </tr>`;
                });
            }
            document.getElementById('tbody-denda').innerHTML = html;
            document.getElementById('denda-total').innerText = 'Rp ' + formatter.format(json.total_denda);
        }
    }
    
    function tutupDenda() {
        document.getElementById('modal-denda').classList.add('hidden');
        document.getElementById('modal-denda').classList.remove('flex');
    }
    
    function cetakDenda() {
        const originalContent = document.body.innerHTML;
        const printArea = document.getElementById('area-cetak-denda').outerHTML;
        document.body.innerHTML = printArea;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload(); // Reload untuk mengembalikan event listener
    }
    
    function bukaSlipGaji(data) {
        document.getElementById('modal-slip').classList.remove('hidden');
        document.getElementById('modal-slip').classList.add('flex');
        
        // Identitas
        document.getElementById('slip-nip').innerText = data.nip;
        document.getElementById('slip-nama').innerText = data.nama_lengkap;
        document.getElementById('slip-ttd').innerText = data.nama_lengkap;
        document.getElementById('slip-jabatan').innerText = data.jabatan || '-';
        document.getElementById('slip-cabang').innerText = data.nama_cabang;
        document.getElementById('slip-pajak').innerText = data.status_pajak_snapshot || 'TK/0';
        document.getElementById('slip-periode').innerText = namaBulan[data.bulan] + ' ' + data.tahun;
        
        // --- Pendapatan Kiri Atas ---
        const gapok = parseFloat(data.nilai_gaji_pokok) || 0;
        const tjab  = parseFloat(data.nilai_tunj_jabatan) || 0;
        const ttrans = parseFloat(data.nilai_tunj_transportasi) || 0;
        const tmakan = parseFloat(data.nilai_tunj_makan) || 0;
        const thadir = parseFloat(data.nilai_tunj_kehadiran) || 0;
        const tlain = parseFloat(data.nilai_tunj_lainnya) || 0;
        const overtime = parseFloat(data.nilai_overtime) || 0;
        const bonus    = parseFloat(data.nilai_bonus) || 0;
        
        document.getElementById('p-gaji').innerText = formatter.format(gapok);
        document.getElementById('p-bonus').innerText = formatter.format(bonus);
        document.getElementById('p-tjab').innerText = formatter.format(tjab);
        document.getElementById('p-ttrans').innerText = formatter.format(ttrans);
        document.getElementById('p-tmakan').innerText = formatter.format(tmakan);
        document.getElementById('p-thadir').innerText = formatter.format(thadir);
        document.getElementById('p-overtime').innerText = formatter.format(overtime);
        document.getElementById('p-tlain').innerText = formatter.format(tlain);
        
        const sub1 = gapok + bonus + tjab + ttrans + tmakan + thadir + overtime + tlain;
        document.getElementById('p-subtotal1').innerText = formatter.format(sub1);
        
        // --- Tunjangan Perusahaan (Kiri Bawah) ---
        const jht37 = parseFloat(data.tunj_jht_37) || 0;
        const jkk = parseFloat(data.tunj_jkk_024) || 0;
        const jk = parseFloat(data.tunj_jk_03) || 0;
        const bpjs4 = parseFloat(data.tunj_bpjs_kes_4) || 0;
        const jp2 = parseFloat(data.tunj_jp_2) || 0;
        
        document.getElementById('p-jht37').innerText = formatter.format(jht37);
        document.getElementById('p-jkk').innerText = formatter.format(jkk);
        document.getElementById('p-jk').innerText = formatter.format(jk);
        document.getElementById('p-bpjs4').innerText = formatter.format(bpjs4);
        document.getElementById('p-jp2').innerText = formatter.format(jp2);
        
        const sub2 = jht37 + jkk + jk + bpjs4 + jp2;
        document.getElementById('p-subtotal2').innerText = formatter.format(sub2);
        
        // Total Gaji
        const totalPendapatan = sub1 + sub2;
        document.getElementById('total-pendapatan').innerText = formatter.format(totalPendapatan);
        
        // --- Pengurangan (Kanan) ---
        const pph21 = parseFloat(data.pot_pph21) || 0;
        const jht2 = parseFloat(data.pot_jht_2) || 0;
        const bpjs1 = parseFloat(data.pot_bpjs_kes_1) || 0;
        const jp1 = parseFloat(data.pot_jp_1) || 0;
        
        document.getElementById('m-pph21').innerText = formatter.format(pph21);
        document.getElementById('m-jht2').innerText = formatter.format(jht2);
        document.getElementById('m-jht37').innerText = formatter.format(jht37);
        document.getElementById('m-jkk').innerText = formatter.format(jkk);
        document.getElementById('m-jk').innerText = formatter.format(jk);
        document.getElementById('m-bpjs1').innerText = formatter.format(bpjs1);
        document.getElementById('m-bpjs4').innerText = formatter.format(bpjs4);
        document.getElementById('m-jp1').innerText = formatter.format(jp1);
        document.getElementById('m-jp2').innerText = formatter.format(jp2);
        
        const totalPengurangan = pph21 + jht2 + jht37 + jkk + jk + bpjs1 + bpjs4 + jp1 + jp2;
        document.getElementById('total-pengurangan').innerText = formatter.format(totalPengurangan);
        
        // Total Diterima (Official = Tanpa Denda Telat di slip)
        const totalDiterima = totalPendapatan - totalPengurangan;
        document.getElementById('total-diterima').innerText = formatter.format(totalDiterima);
    }
    
    function tutupSlip() {
        document.getElementById('modal-slip').classList.add('hidden');
        document.getElementById('modal-slip').classList.remove('flex');
    }
    
    function cetakSlip() {
        const originalContent = document.body.innerHTML;
        const printArea = document.getElementById('area-cetak-slip').outerHTML;
        document.body.innerHTML = printArea;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }

    async function generateGaji() {
        const cabangSelect = document.getElementById('generate_cabang');
        const id_cabang = cabangSelect ? cabangSelect.value : '';
        
        if (id_cabang === '') {
            Swal.fire('Pilih Cabang', 'Silakan pilih cabang terlebih dahulu sebelum melakukan generate gaji.', 'warning');
            return;
        }

        const nama_cabang = cabangSelect.options[cabangSelect.selectedIndex].text;
        
        if (id_cabang === 'all') {
            const warning = await Swal.fire({
                title: 'Peringatan: Semua Cabang!',
                html: 'Anda memilih <b>Semua Cabang</b>.<br>Proses ini memakan waktu sangat lama jika data banyak. Anda yakin ingin melanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tetap Generate',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            });
            if (!warning.isConfirmed) return;
        }

        const { isConfirmed } = await Swal.fire({
            title: 'Generate Penggajian?',
            html: `Sistem akan menghitung otomatis gaji bersih berdasarkan data absensi bulan <b><?= $nama_bulan[$bulan] ?> <?= $tahun ?></b> untuk <b>${nama_cabang}</b>.`,
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Ya, Generate!', cancelButtonText: 'Batal',
            confirmButtonColor: '#f59e0b'
        });
        if (!isConfirmed) return;

        Swal.fire({ title: 'Memproses...', text: 'Menghitung kalkulasi gaji pegawai...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const postCabang = id_cabang === 'all' ? '' : id_cabang;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/generate_gaji', {
            method: 'POST',
            body: new URLSearchParams({ 
                bulan: '<?= $bulan ?>', 
                tahun: '<?= $tahun ?>', 
                id_cabang: postCabang,
                csrf_token: '<?= csrf_token() ?>' 
            })
        });
        const data = await resp.json();
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    async function publishGaji() {
        const cabangSelect = document.getElementById('generate_cabang');
        const id_cabang = cabangSelect ? cabangSelect.value : 'all';
        const nama_cabang = cabangSelect ? cabangSelect.options[cabangSelect.selectedIndex].text : 'Semua Cabang';

        const { isConfirmed } = await Swal.fire({
            title: 'Publish Gaji?',
            html: `Data gaji <b><?= $nama_bulan[$bulan] ?> <?= $tahun ?></b> untuk <b>${nama_cabang}</b> akan dipublikasikan dan bisa dilihat oleh pegawai. Tindakan ini <b>tidak dapat dibatalkan</b>.`,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, Publish!', cancelButtonText: 'Batal',
            confirmButtonColor: '#22c55e'
        });
        if (!isConfirmed) return;

        Swal.fire({ title: 'Memproses...', text: 'Mempublikasikan gaji...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const resp = await fetch('<?= BASE_URL ?>/superadmin/publish_gaji', {
            method: 'POST',
            body: new URLSearchParams({ 
                bulan: '<?= $bulan ?>', 
                tahun: '<?= $tahun ?>', 
                id_cabang: id_cabang,
                csrf_token: '<?= csrf_token() ?>' 
            })
        });
        const data = await resp.json();
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    function filterGaji() {
        const selectedCabang = document.getElementById('generate_cabang').value;
        const rows = document.querySelectorAll('.row-gaji');
        
        let countVisible = 0;
        let totalGapok = 0;
        let totalPotongan = 0;
        let totalBpjs = 0;
        let totalBersih = 0;

        const hasTable = rows.length > 0;
        
        if (hasTable) {
            rows.forEach(row => {
                const rowCabang = row.getAttribute('data-cabang');
                if (selectedCabang === 'all' || rowCabang === selectedCabang) {
                    row.style.display = '';
                    countVisible++;
                    totalGapok += parseFloat(row.getAttribute('data-gapok')) || 0;
                    totalPotongan += parseFloat(row.getAttribute('data-potongan')) || 0;
                    totalBpjs += parseFloat(row.getAttribute('data-bpjs')) || 0;
                    totalBersih += parseFloat(row.getAttribute('data-bersih')) || 0;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update Footer Totals
            const footerGapok = document.getElementById('total-footer-gapok');
            const footerPotongan = document.getElementById('total-footer-potongan');
            const footerBpjs = document.getElementById('total-footer-bpjs');
            const footerBersih = document.getElementById('total-footer-bersih');

            if (footerGapok) footerGapok.innerText = 'Rp ' + formatter.format(totalGapok);
            if (footerPotongan) footerPotongan.innerText = '- Rp ' + formatter.format(totalPotongan);
            if (footerBpjs) footerBpjs.innerText = '- Rp ' + formatter.format(totalBpjs);
            if (footerBersih) footerBersih.innerText = 'Rp ' + formatter.format(totalBersih);

            // Update Summary Cards
            const cardPegawai = document.getElementById('ringkasan_pegawai');
            const cardPotongan = document.getElementById('ringkasan_potongan');
            const cardGajiBersih = document.getElementById('ringkasan_gaji_bersih');

            if (cardPegawai) cardPegawai.innerText = countVisible;
            if (cardPotongan) cardPotongan.innerText = 'Rp ' + formatter.format(totalPotongan);
            if (cardGajiBersih) cardGajiBersih.innerText = 'Rp ' + formatter.format(totalBersih);
        } else {
            // When there is no table generated yet, filter the employee card count from PHP arrays
            const cabangCounts = <?= json_encode($cabang_counts ?? []) ?>;
            const cardPegawai = document.getElementById('ringkasan_pegawai');
            if (cardPegawai) {
                if (selectedCabang === 'all') {
                    cardPegawai.innerText = <?= count($ringkasan) ?>;
                } else {
                    cardPegawai.innerText = cabangCounts[selectedCabang] || 0;
                }
            }
        }
    }

    // Call initially to set up correct totals
    document.addEventListener('DOMContentLoaded', filterGaji);
</script>
