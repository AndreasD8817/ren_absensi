<?php
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!-- Halaman Kelola Insentif - Superadmin -->
<div class="flex h-screen overflow-hidden bg-gray-50">

    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">

        <!-- Header + Filter -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Insentif Cabang</h2>
                <p class="text-gray-500 text-sm mt-1">Distribusi insentif global secara merata ke seluruh pegawai cabang</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Filter Periode -->
                <form method="GET" action="<?= BASE_URL ?>/superadmin/insentif" class="flex items-center gap-2 bg-white px-4 py-2.5 rounded-xl shadow-sm border border-gray-100">
                    <select name="bulan" class="text-sm font-semibold text-gray-700 bg-transparent border-0 outline-none">
                        <?php for ($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i==$bulan?'selected':'' ?>><?= $nama_bulan[$i] ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="tahun" class="text-sm font-semibold text-gray-700 bg-transparent border-0 outline-none">
                        <?php for ($y=date('Y'); $y>=date('Y')-3; $y--): ?>
                        <option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="px-3 py-1 bg-primary text-white text-xs font-bold rounded-lg hover:bg-blue-800">Tampilkan</button>
                </form>
                <!-- Tombol Tambah -->
                <button onclick="bukaModal()" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-md hover:bg-emerald-700 active:scale-95 transition-all">
                    <i class="fa-solid fa-plus"></i> Buat Insentif Baru
                </button>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-8 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-amber-500 text-xl mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="text-sm font-semibold text-amber-800">Cara Kerja Modul Insentif</p>
                <p class="text-xs text-amber-700 mt-1">Insentif bersifat <b>terpisah dari slip gaji</b>. Superadmin memasukkan nilai global per cabang → sistem membagi rata ke seluruh pegawai aktif → klik <b>Publish</b> saat insentif siap dicairkan (misalnya tanggal 15-20). Pegawai akan melihat slip insentif terpisah di dashboard mereka.</p>
            </div>
        </div>

        <!-- Daftar Kartu Insentif -->
        <?php if (empty($list_insentif)): ?>
        <div class="bg-white rounded-2xl p-16 text-center border border-dashed border-gray-200">
            <i class="fa-solid fa-gift text-5xl text-gray-200 mb-4"></i>
            <h3 class="text-lg font-bold text-gray-500">Belum Ada Insentif di Periode Ini</h3>
            <p class="text-gray-400 text-sm mt-2">Klik <b>"Buat Insentif Baru"</b> untuk mulai mendistribusikan insentif ke pegawai.</p>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($list_insentif as $ins): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 gap-4">
                    <!-- Info Utama -->
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-gift text-white text-2xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-800 text-lg"><?= esc($ins['nama_cabang']) ?></h3>
                                <?php if ($ins['status'] === 'published'): ?>
                                <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full"><i class="fa-solid fa-check-circle mr-1"></i>Published</span>
                                <?php else: ?>
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full"><i class="fa-solid fa-clock mr-1"></i>Draft</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500">
                                <?= $ins['keterangan'] ?: 'Insentif Bulanan' ?> &bull;
                                <span class="font-medium"><?= $nama_bulan[$ins['bulan']] ?> <?= $ins['tahun'] ?></span> &bull;
                                Dibuat: <?= date('d M Y', strtotime($ins['tanggal_input'])) ?>
                            </p>
                        </div>
                    </div>
                    <!-- Nilai & Aksi -->
                    <div class="flex flex-col md:items-end gap-2 md:ml-auto">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-black text-emerald-600">Rp <?= number_format($ins['total_nilai'],0,',','.') ?></p>
                            <p class="text-sm text-gray-400">/ <?= $ins['jumlah_penerima'] ?> pegawai</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            ≈ <b class="text-gray-700">Rp <?= $ins['jumlah_penerima'] > 0 ? number_format($ins['total_nilai']/$ins['jumlah_penerima'],0,',','.') : 0 ?></b> per orang
                        </p>
                        <div class="flex gap-2 mt-1">
                            <button onclick="lihatDetail(<?= $ins['id_insentif'] ?>, '<?= esc($ins['nama_cabang']) ?>')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-blue-50 text-primary rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fa-solid fa-list-ul"></i> Detail
                            </button>
                            <?php if ($ins['status'] === 'draft'): ?>
                            <button onclick="publishInsentif(<?= $ins['id_insentif'] ?>, '<?= esc($ins['nama_cabang']) ?>')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors">
                                <i class="fa-solid fa-paper-plane"></i> Publish
                            </button>
                            <button onclick="hapusInsentif(<?= $ins['id_insentif'] ?>)" class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- ==================== MODAL BUAT INSENTIF BARU ==================== -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-gift text-emerald-500 mr-2"></i>Buat Insentif Baru</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-insentif" onsubmit="submitInsentif(event)" class="p-6 space-y-4">
    <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Cabang Penerima *</label>
                <select name="id_cabang" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">-- Pilih Cabang --</option>
                    <?php foreach ($cabang as $c): ?>
                    <option value="<?= $c['id_cabang'] ?>"><?= esc($c['nama_cabang']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan *</label>
                    <select name="bulan" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <?php for ($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i==$bulan?'selected':'' ?>><?= $nama_bulan[$i] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun *</label>
                    <select name="tahun" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <?php for ($y=date('Y'); $y>=date('Y')-2; $y--): ?>
                        <option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Total Nilai Insentif (Rp) *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                    <input name="total_nilai" type="number" required min="1" id="input-total-nilai"
                           class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none"
                           placeholder="Contoh: 10000000" oninput="previewBagi()">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input name="keterangan" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Contoh: Insentif Kinerja Semester 1">
            </div>

            <!-- Preview Kalkulasi -->
            <div id="preview-bagi" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-emerald-700 mb-2"><i class="fa-solid fa-calculator mr-1"></i>Preview Kalkulasi</p>
                <p class="text-xs text-emerald-600">Total: <b id="prev-total">—</b></p>
                <p class="text-xs text-emerald-600 mt-1">Catatan: Nilai per orang dihitung saat disimpan berdasarkan jumlah pegawai aktif di cabang yang dipilih.</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModal()" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" id="btn-submit" class="px-5 py-2.5 text-sm bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-colors">
                    <i class="fa-solid fa-calculator mr-1"></i> Hitung & Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL DETAIL PENERIMA ==================== -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center p-6 border-b flex-shrink-0">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-list-ul text-primary mr-2"></i>Detail Penerima Insentif — <span id="judul-detail"></span></h3>
            <button onclick="tutupDetail()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div id="isi-detail" class="overflow-y-auto p-6">
            <div class="text-center text-gray-400 py-8"><i class="fa-solid fa-spinner fa-spin text-3xl"></i></div>
        </div>
    </div>
</div>

<script>
    function bukaModal() {
        document.getElementById('form-insentif').reset();
        document.getElementById('preview-bagi').classList.add('hidden');
        document.getElementById('modal-tambah').classList.remove('hidden');
        document.getElementById('modal-tambah').classList.add('flex');
    }
    function tutupModal() {
        document.getElementById('modal-tambah').classList.add('hidden');
        document.getElementById('modal-tambah').classList.remove('flex');
    }
    function tutupDetail() {
        document.getElementById('modal-detail').classList.add('hidden');
        document.getElementById('modal-detail').classList.remove('flex');
    }

    function previewBagi() {
        const val = parseFloat(document.getElementById('input-total-nilai').value) || 0;
        const preview = document.getElementById('preview-bagi');
        if (val > 0) {
            document.getElementById('prev-total').textContent = 'Rp ' + val.toLocaleString('id-ID');
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    }

    async function submitInsentif(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Memproses...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_insentif', {
            method: 'POST', body: new FormData(document.getElementById('form-insentif'))
        });
        const data = await resp.json();
        btn.innerHTML = '<i class="fa-solid fa-calculator mr-1"></i> Hitung & Simpan';
        btn.disabled = false;

        if (data.status === 'success') {
            tutupModal();
            Swal.fire({
                title: 'Berhasil Didistribusikan!',
                html: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    async function publishInsentif(id, nama_cabang) {
        const { isConfirmed } = await Swal.fire({
            title: 'Publish Insentif?',
            html: `Insentif untuk <b>${nama_cabang}</b> akan dipublikasikan dan <b>bisa dilihat oleh pegawai</b>. Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, Publish!', cancelButtonText: 'Batal',
            confirmButtonColor: '#059669'
        });
        if (!isConfirmed) return;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/publish_insentif', {
            method: 'POST', body: new URLSearchParams({ id_insentif: id, csrf_token: '<?= csrf_token() ?>' })
        });
        const data = await resp.json();
        if (data.status === 'success') Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        else Swal.fire('Gagal', data.message, 'error');
    }

    async function hapusInsentif(id) {
        const { isConfirmed } = await Swal.fire({
            title: 'Hapus Data Insentif?', text: 'Data distribusi ke pegawai juga akan terhapus.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!', confirmButtonColor: '#d33', cancelButtonText: 'Batal'
        });
        if (!isConfirmed) return;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/hapus_insentif', {
            method: 'POST', body: new URLSearchParams({ id_insentif: id, csrf_token: '<?= csrf_token() ?>' })
        });
        const data = await resp.json();
        if (data.status === 'success') Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        else Swal.fire('Gagal', data.message, 'error');
    }

    async function lihatDetail(id, nama_cabang) {
        document.getElementById('judul-detail').textContent = nama_cabang;
        document.getElementById('isi-detail').innerHTML = '<div class="text-center text-gray-400 py-8"><i class="fa-solid fa-spinner fa-spin text-3xl"></i></div>';
        document.getElementById('modal-detail').classList.remove('hidden');
        document.getElementById('modal-detail').classList.add('flex');

        const resp = await fetch(`<?= BASE_URL ?>/superadmin/detail_insentif?id=${id}`);
        const res = await resp.json();
        if (res.status === 'success') {
            let html = '<table class="w-full text-sm"><thead class="bg-gray-50"><tr><th class="text-left py-2 px-3 text-xs text-gray-500">Pegawai</th><th class="text-right py-2 px-3 text-xs text-gray-500">Insentif Diterima</th><th class="text-center py-2 px-3 text-xs text-gray-500">Status</th></tr></thead><tbody class="divide-y divide-gray-100">';
            res.data.forEach(p => {
                const badge = p.status === 'published'
                    ? '<span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">Published</span>'
                    : '<span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">Draft</span>';
                html += `<tr class="hover:bg-gray-50">
                    <td class="py-3 px-3"><p class="font-semibold text-gray-800">${p.nama_lengkap}</p><p class="text-xs text-gray-400">${p.nip} · ${p.jabatan}</p></td>
                    <td class="py-3 px-3 text-right font-bold text-emerald-600">Rp ${parseFloat(p.nilai_didapat).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-3 text-center">${badge}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('isi-detail').innerHTML = html;
        }
    }
</script>
