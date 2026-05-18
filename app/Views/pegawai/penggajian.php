<!-- Kontainer Mobile Khusus Pegawai -->
<div class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 shadow-2xl relative overflow-hidden font-sans">
    
    <!-- Latar Belakang Desain -->
    <div class="absolute top-0 left-0 right-0 h-48 bg-gradient-to-b from-primary to-blue-800 rounded-b-[40px] z-0"></div>
    <div class="absolute top-10 right-[-20px] w-32 h-32 bg-white opacity-10 rounded-full blur-2xl z-0"></div>

    <!-- Header -->
    <header class="relative z-10 px-6 pt-10 pb-4 flex items-center justify-between text-white">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Slip Gaji</h2>
            <p class="text-blue-200 text-sm mt-0.5">Riwayat Pembayaran</p>
        </div>
        <a href="<?= BASE_URL ?>/pegawai" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 active:scale-90 transition-transform">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
    </header>

    <!-- Konten Scrollable -->
    <div class="relative z-10 px-5 mt-2 space-y-4">
        <?php 
        $nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        if (empty($riwayat_gaji)): 
        ?>
        <div class="bg-white rounded-3xl p-8 text-center border border-dashed border-gray-200 shadow-sm mt-8">
            <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-file-invoice-dollar text-3xl text-blue-300"></i>
            </div>
            <h3 class="font-bold text-gray-800 mb-1">Belum Ada Gaji</h3>
            <p class="text-sm text-gray-500">Riwayat penggajian Anda akan muncul di sini setelah diterbitkan oleh perusahaan.</p>
        </div>
        <?php else: ?>
            <?php foreach ($riwayat_gaji as $g): ?>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-3xl"></div>
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-0.5">Periode Penggajian</p>
                        <h4 class="font-bold text-gray-800 text-lg"><?= $nama_bulan[$g['bulan']] ?> <?= $g['tahun'] ?></h4>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-primary">
                        <i class="fa-solid fa-money-check-dollar"></i>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-3 mb-4 flex justify-between items-center border border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500">Total Diterima (Netto)</p>
                        <p class="font-bold text-green-600">Rp <?= number_format($g['total_gaji_bersih'], 0, ',', '.') ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total Potongan</p>
                        <p class="font-bold text-red-500">Rp <?= number_format($g['total_potongan_telat_alfa'], 0, ',', '.') ?></p>
                    </div>
                </div>

                <button onclick='lihatSlip(<?= json_encode($g) ?>, <?= json_encode($pegawai) ?>)' class="w-full py-2.5 bg-white border border-primary text-primary rounded-xl font-bold text-sm hover:bg-blue-50 active:bg-blue-100 transition-colors flex items-center justify-center gap-2">
                    <i class="fa-regular fa-eye"></i> Lihat Slip Detail
                </button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== MODAL SLIP GAJI (TAILWIND/PRINT) ==================== -->
<div id="modal-slip" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/60 backdrop-blur-sm p-2 md:p-4 overflow-y-auto pt-10 pb-10">
    <div class="bg-white shadow-2xl w-full max-w-4xl font-mono text-sm relative mt-4">
        
        <!-- Action Buttons (Print & Close) -->
        <div class="absolute top-4 right-4 flex flex-col md:flex-row gap-2 z-40 print:hidden">
            <button onclick="cetakSlip()" class="px-4 py-2 bg-primary text-white rounded shadow-sm active:scale-95 transition-transform text-xs font-sans flex items-center justify-center">
                <i class="fa-solid fa-print mr-2"></i>Cetak PDF
            </button>
            <button onclick="tutupSlip()" class="px-4 py-2 bg-red-100 text-red-600 rounded shadow-sm active:scale-95 transition-transform text-xs font-sans flex items-center justify-center">
                <i class="fa-solid fa-xmark mr-2"></i>Tutup
            </button>
        </div>
        
        <div id="area-cetak-slip" class="p-6 md:p-10 bg-white text-black min-w-[700px] md:min-w-0 overflow-x-auto print:overflow-visible print:min-w-0">
            <!-- Header Perusahaan -->
            <div class="mb-4">
                <h1 class="font-bold text-lg leading-tight uppercase">PT REN</h1>
                <p class="text-xs">Kantor Pusat PT REN</p>
                <p class="text-xs">Indonesia</p>
            </div>
            
            <h2 class="text-center font-bold text-lg mb-6 tracking-widest border-b-2 border-black pb-4">PAYROLL SHEET</h2>
            
            <!-- Identitas Pegawai -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1 mb-8 text-[11px] md:text-xs">
                <div class="flex"><div class="w-24 font-bold">NIK</div><div>: <span id="slip-nip"></span></div></div>
                <div class="flex"><div class="w-32 font-bold">Periode</div><div>: <span id="slip-periode"></span></div></div>
                <div class="flex"><div class="w-24 font-bold">Nama</div><div>: <span id="slip-nama"></span></div></div>
                <div class="flex"><div class="w-32 font-bold">Status Pajak</div><div>: <span id="slip-pajak"></span></div></div>
                <div class="flex"><div class="w-24 font-bold">Jabatan</div><div>: <span id="slip-jabatan"></span></div></div>
                <div class="flex"><div class="w-32 font-bold">Lokasi Kerja</div><div>: <span id="slip-cabang"></span></div></div>
            </div>
            
            <div class="border-t-2 border-black mb-4"></div>
            
            <!-- Dua Kolom Pendapatan & Pengurangan -->
            <div class="grid grid-cols-2 gap-6 text-[10px] md:text-xs">
                <!-- Kolom Kiri: PENDAPATAN -->
                <div>
                    <h3 class="font-bold mb-4 uppercase">PENDAPATAN :</h3>
                    
                    <div class="flex justify-between mb-1"><div class="flex-1">- Gaji Pokok</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-gaji"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Tunjangan Jabatan</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-tjab"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Tunjangan* Transportasi</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-ttrans"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1 pl-4 md:pl-16">Makan</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-tmakan"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1 pl-4 md:pl-16">Kehadiran</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-thadir"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Overtime</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-overtime">0</div></div>
                    <div class="flex justify-between mb-2"><div class="flex-1">- Lainnya</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-tlain"></div></div>
                    
                    <div class="flex justify-between font-bold border-t border-black pt-1 mb-6">
                        <div class="flex-1">Gaji & Tunjangan</div><div class="w-20 md:w-24 text-right" id="p-subtotal1"></div>
                    </div>
                    
                    <div class="flex justify-between mb-1"><div class="flex-1">- JHT (3.70%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-jht37"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JKK (0.24%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-jkk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JK (0.30%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-jk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- BPJS Kesehatan (4%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-bpjs4"></div></div>
                    <div class="flex justify-between mb-2"><div class="flex-1">- Jaminan Pensiun (2%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="p-jp2"></div></div>
                    
                    <div class="border-t-2 border-black mb-1"></div>
                    <div class="flex justify-between mb-4 font-bold"><div class="w-full text-right" id="p-subtotal2"></div></div>
                    
                    <div class="flex justify-between font-bold mb-4"><div class="w-24 md:w-32">Total Gaji</div><div class="w-4">:</div><div class="flex-1 text-right" id="total-pendapatan"></div></div>
                    <div class="flex justify-between font-bold mb-2 bg-yellow-100 p-1 -ml-1 rounded"><div class="w-24 md:w-32">Total Diterima</div><div class="w-4">:</div><div class="flex-1 text-right text-sm" id="total-diterima"></div></div>
                </div>
                
                <!-- Kolom Kanan: PENGURANGAN -->
                <div>
                    <h3 class="font-bold mb-4 uppercase">PENGURANGAN :</h3>
                    
                    <div class="flex justify-between mb-1"><div class="flex-1">- Pinjaman Karyawan</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right">0</div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Pajak Penghasilan (PPh 21)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-pph21"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JHT (2%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-jht2"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JHT (3.70%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-jht37"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JKK (0.24%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-jkk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- JK (0.30%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-jk"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- BPJS Kesehatan (1%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-bpjs1"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- BPJS Kesehatan (4%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-bpjs4"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Jaminan Pensiun (1%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-jp1"></div></div>
                    <div class="flex justify-between mb-1"><div class="flex-1">- Jaminan Pensiun (2%)</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="m-jp2"></div></div>
                    <div class="flex justify-between mb-2"><div class="flex-1">- Lainnya</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right">0</div></div>
                    
                    <div class="border-t-2 border-black pt-2 mt-4 flex justify-between font-bold">
                        <div class="flex-1">Total Pengurangan</div><div class="w-4">:</div><div class="w-20 md:w-24 text-right" id="total-pengurangan"></div>
                    </div>
                </div>
            </div>
            
            <div class="border-t-2 border-black mt-8 pt-2">
                <p class="text-[9px] md:text-[10px] italic">* Diberikan utuh jika tanpa absensi dan ijin, mengikuti ketentuan perusahaan</p>
            </div>
            
            <div class="mt-12 pb-6">
                <p class="text-[10px] md:text-xs mb-12">Diterima oleh :</p>
                <p class="text-[10px] md:text-xs font-bold underline" id="slip-ttd"></p>
            </div>
            
        </div>
    </div>
</div>

<!-- Menggunakan html2pdf agar format terjamin rapi pada versi mobile jika window.print() berantakan -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(parseFloat(angka) || 0);
};

const namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function lihatSlip(g, user) {
    document.getElementById('slip-nip').innerText = user.nip || '-';
    document.getElementById('slip-nama').innerText = user.nama_lengkap;
    document.getElementById('slip-jabatan').innerText = user.jabatan;
    document.getElementById('slip-pajak').innerText = g.status_pajak_snapshot || '-';
    document.getElementById('slip-cabang').innerText = 'Kantor Cabang';
    document.getElementById('slip-periode').innerText = namaBulan[g.bulan] + ' ' + g.tahun;
    document.getElementById('slip-ttd').innerText = user.nama_lengkap;

    // PENDAPATAN
    document.getElementById('p-gaji').innerText = formatRupiah(g.nilai_gaji_pokok);
    document.getElementById('p-tjab').innerText = formatRupiah(g.nilai_tunj_jabatan);
    document.getElementById('p-ttrans').innerText = formatRupiah(g.nilai_tunj_transportasi);
    document.getElementById('p-tmakan').innerText = formatRupiah(g.nilai_tunj_makan);
    document.getElementById('p-thadir').innerText = formatRupiah(g.nilai_tunj_kehadiran);
    document.getElementById('p-tlain').innerText = formatRupiah(g.nilai_tunj_lainnya);
    
    // OVERTIME JIKA ADA (TAHAP 5)
    let overtime = parseFloat(g.nilai_overtime || 0);
    document.getElementById('p-overtime').innerText = formatRupiah(overtime);

    let subtotal1 = parseFloat(g.nilai_gaji_pokok) + parseFloat(g.nilai_tunj_jabatan) + parseFloat(g.nilai_tunj_transportasi) + parseFloat(g.nilai_tunj_makan) + parseFloat(g.nilai_tunj_kehadiran) + parseFloat(g.nilai_tunj_lainnya) + overtime;
    document.getElementById('p-subtotal1').innerText = formatRupiah(subtotal1);

    document.getElementById('p-jht37').innerText = formatRupiah(g.tunj_jht_37);
    document.getElementById('p-jkk').innerText = formatRupiah(g.tunj_jkk_024);
    document.getElementById('p-jk').innerText = formatRupiah(g.tunj_jk_03);
    document.getElementById('p-bpjs4').innerText = formatRupiah(g.tunj_bpjs_kes_4);
    document.getElementById('p-jp2').innerText = formatRupiah(g.tunj_jp_2);

    let subtotal2 = parseFloat(g.tunj_jht_37) + parseFloat(g.tunj_jkk_024) + parseFloat(g.tunj_jk_03) + parseFloat(g.tunj_bpjs_kes_4) + parseFloat(g.tunj_jp_2);
    document.getElementById('p-subtotal2').innerText = formatRupiah(subtotal2);

    let totalPendapatan = subtotal1 + subtotal2;
    document.getElementById('total-pendapatan').innerText = formatRupiah(totalPendapatan);

    // PENGURANGAN
    document.getElementById('m-pph21').innerText = formatRupiah(g.pot_pph21);
    document.getElementById('m-jht2').innerText = formatRupiah(g.pot_jht_2);
    document.getElementById('m-jht37').innerText = formatRupiah(g.tunj_jht_37);
    document.getElementById('m-jkk').innerText = formatRupiah(g.tunj_jkk_024);
    document.getElementById('m-jk').innerText = formatRupiah(g.tunj_jk_03);
    document.getElementById('m-bpjs1').innerText = formatRupiah(g.pot_bpjs_kes_1);
    document.getElementById('m-bpjs4').innerText = formatRupiah(g.tunj_bpjs_kes_4);
    document.getElementById('m-jp1').innerText = formatRupiah(g.pot_jp_1);
    document.getElementById('m-jp2').innerText = formatRupiah(g.tunj_jp_2);

    // Total pengurangan di slip TANPA memasukkan denda absen
    let totalPenguranganTampil = parseFloat(g.pot_pph21) + parseFloat(g.pot_jht_2) + parseFloat(g.tunj_jht_37) + parseFloat(g.tunj_jkk_024) + parseFloat(g.tunj_jk_03) + parseFloat(g.pot_bpjs_kes_1) + parseFloat(g.tunj_bpjs_kes_4) + parseFloat(g.pot_jp_1) + parseFloat(g.tunj_jp_2);
    document.getElementById('total-pengurangan').innerText = formatRupiah(totalPenguranganTampil);

    let diterimaTampil = totalPendapatan - totalPenguranganTampil;
    document.getElementById('total-diterima').innerText = formatRupiah(diterimaTampil);

    document.getElementById('modal-slip').classList.remove('hidden');
    document.getElementById('modal-slip').classList.add('flex');
}

function tutupSlip() {
    document.getElementById('modal-slip').classList.add('hidden');
    document.getElementById('modal-slip').classList.remove('flex');
}

function cetakSlip() {
    // Pada mode mobile, window.print() kadang tidak optimal, kita pakai html2pdf
    const element = document.getElementById('area-cetak-slip');
    const nama = document.getElementById('slip-nama').innerText;
    const periode = document.getElementById('slip-periode').innerText;
    
    var opt = {
        margin:       10,
        filename:     `Slip_Gaji_${nama}_${periode}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Show loading
    Swal.fire({ title: 'Membuat PDF...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

    html2pdf().set(opt).from(element).save().then(() => {
        Swal.close();
    });
}
</script>
