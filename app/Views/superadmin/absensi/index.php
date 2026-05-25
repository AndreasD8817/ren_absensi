<?php
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$base_foto = BASE_URL . '/uploads/';

// Format string periode
$prev_bulan = $bulan - 1;
$prev_tahun = $tahun;
if ($prev_bulan == 0) {
    $prev_bulan = 12;
    $prev_tahun -= 1;
}
$start_str = "26 " . $nama_bulan[$prev_bulan] . " " . $prev_tahun;
$end_str = "25 " . $nama_bulan[$bulan] . " " . $tahun;
?>
<!-- Halaman Absensi - Superadmin -->
<div class="flex h-screen overflow-hidden bg-gray-50">

    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header + Filter -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Data Absensi Pegawai</h2>
                <p class="text-gray-500 text-sm mt-1">Rekapitulasi kehadiran, keterlambatan, dan alfa bulanan</p>
                <p class="text-blue-700 font-semibold text-xs mt-2 bg-blue-50 border border-blue-100 inline-block px-3 py-1 rounded-full"><i class="fa-solid fa-calendar-days mr-1"></i> Periode Cut-Off: <?= $start_str ?> — <?= $end_str ?></p>
            </div>
            <form method="GET" action="<?= BASE_URL ?>/superadmin/absensi" class="flex items-center gap-3 bg-white px-4 py-3 rounded-2xl shadow-sm border border-gray-100">
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
                <select name="id_cabang" class="text-sm border-0 outline-none focus:ring-0 font-semibold text-gray-700 bg-transparent border-l border-gray-200 pl-3">
                    <option value="">Semua Cabang</option>
                    <?php foreach ($list_cabang as $c): ?>
                    <option value="<?= $c['id_cabang'] ?>" <?= isset($id_cabang) && $c['id_cabang'] == $id_cabang ? 'selected' : '' ?>><?= esc($c['nama_cabang']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-blue-800 transition-colors">Tampilkan</button>
            </form>
        </div>

        <!-- Tombol Export -->
        <div class="flex justify-end mb-4">
            <div class="flex gap-2">
                <a href="<?= BASE_URL ?>/superadmin/export_absensi?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&format=excel" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 text-sm font-bold rounded-xl hover:bg-green-200 transition-colors">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
                <a href="<?= BASE_URL ?>/superadmin/export_absensi?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&format=pdf" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 text-sm font-bold rounded-xl hover:bg-red-200 transition-colors">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Tabel Rekap Absensi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <p class="font-semibold text-gray-700">Total: <span class="text-primary font-bold" id="totalRecords"><?= count($ringkasan) ?> Pegawai</span></p>
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="searchInput" onkeyup="filterAndPaginate()" placeholder="Cari nama pegawai..." class="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none w-full">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tabelAbsensi">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Pegawai</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Cabang</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Hadir</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Terlambat (x)</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Menit Telat</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Alfa (x)</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="tbody-absensi">
                        <?php if (empty($ringkasan)): ?>
                        <tr class="no-data-row"><td colspan="7" class="px-6 py-10 text-center text-gray-400">Tidak ada data pegawai aktif atau absensi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ringkasan as $r): ?>
                            <tr id="row-user-<?= $r['id_user'] ?>" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($r['nama_lengkap']) ?>&size=36&background=random" class="w-9 h-9 rounded-full" alt="">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= esc($r['nama_lengkap']) ?></p>
                                            <p class="text-gray-400 text-xs"><?= esc($r['nip']) ?> - <?= esc($r['jabatan']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium"><?= esc($r['nama_cabang']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="val-hadir inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 font-bold"><?= $r['total_hadir'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="val-telat inline-flex items-center justify-center w-8 h-8 rounded-full <?= $r['total_telat'] > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-50 text-gray-500' ?> font-bold">
                                        <?= $r['total_telat'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-red-500"><span class="val-menit"><?= $r['total_menit_terlambat'] ?? 0 ?></span> m</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="val-alfa inline-flex items-center justify-center w-8 h-8 rounded-full <?= $r['total_alfa'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-500' ?> font-bold">
                                        <?= $r['total_alfa'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="lihatDetail(<?= $r['id_user'] ?>, '<?= esc($r['nama_lengkap']) ?>')" 
                                        class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold hover:bg-blue-800 transition-colors">
                                        <i class="fa-solid fa-eye mr-1"></i>Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm" id="paginationControls">
                <p class="text-gray-500 font-medium" id="paginationInfo">Menampilkan 1-10 dari 50 pegawai</p>
                <div class="flex gap-2">
                    <button onclick="prevPage()" id="btnPrev" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 disabled:opacity-50 disabled:hover:bg-transparent transition-colors">
                        Sebelumnya
                    </button>
                    <button onclick="nextPage()" id="btnNext" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 disabled:opacity-50 disabled:hover:bg-transparent transition-colors">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Detail Absensi Harian -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl mx-4 flex flex-col" style="max-height: 90vh;">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-3xl flex-shrink-0">
            <div>
                <h3 class="font-bold text-gray-800" id="modal-detail-nama">Detail Absensi</h3>
                <p class="text-xs text-gray-500"><?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
            </div>
            <div class="flex items-center gap-2 print:hidden">
                <button onclick="cetakDetailAbsensi()" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors shadow-sm">
                    <i class="fa-solid fa-print mr-1"></i> Cetak PDF
                </button>
                <button onclick="tutupModal('modal-detail')" class="text-gray-400 hover:text-red-500 transition-colors px-2">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
        </div>
        <div class="overflow-y-auto flex-1 p-1">
            <table class="w-full text-xs" id="tabel-detail">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-3 font-bold text-gray-500 uppercase">Tanggal</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500 uppercase">Status</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500 uppercase">Jam Masuk</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500 uppercase">Jam Pulang</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500 uppercase">Menit Telat</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500 uppercase">Bukti</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-500 uppercase print:hidden">Edit</th>
                    </tr>
                </thead>
                <tbody id="tbody-detail" class="divide-y divide-gray-100">
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Absensi -->
<div id="modal-edit" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-3xl">
            <h3 class="font-bold text-gray-800">Edit Data Absensi</h3>
            <button onclick="tutupModal('modal-edit')" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-edit" onsubmit="simpanEdit(event)" class="p-6 space-y-4">
    <?= csrf_field() ?>
            <input type="hidden" name="id_absensi" id="edit-id_absensi">
            
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                <select name="status" id="edit-status" onchange="toggleJamFields(this.value)" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary">
                    <option value="hadir">Hadir</option>
                    <option value="telat">Terlambat</option>
                    <option value="alfa">Alfa (Tidak Hadir)</option>
                </select>
            </div>

            <div id="jam-fields">
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Masuk</label>
                    <input type="time" name="jam_masuk" id="edit-jam_masuk" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary text-gray-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Pulang</label>
                    <input type="time" name="jam_pulang" id="edit-jam_pulang" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary text-gray-700">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-edit')" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200">Batal</button>
                <button type="submit" id="btn-simpan-edit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-blue-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Foto Preview -->
<div id="modal-foto" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80" onclick="tutupModal('modal-foto')">
    <img id="foto-preview" src="" class="max-h-[80vh] max-w-[90vw] rounded-2xl shadow-2xl object-contain">
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const BULAN = <?= $bulan ?>;
const TAHUN = <?= $tahun ?>;
const BASE_FOTO = '<?= $base_foto ?>';

let currentUserId = null;

const statusBadge = {
    hadir:  '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full font-bold">Hadir</span>',
    telat:  '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full font-bold">Terlambat</span>',
    alfa:   '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full font-bold">Alfa</span>',
    cuti:   '<span class="px-2 py-1 bg-teal-100 text-teal-700 rounded-full font-bold">Cuti</span>',
    libur:  '<span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full font-bold">Libur</span>',
    weekend:'<span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full font-bold">Libur (Weekend)</span>',
};

function bukaModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function tutupModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('flex');
    m.classList.add('hidden');
}

async function lihatDetail(id_user, nama) {
    currentUserId = id_user;
    document.getElementById('modal-detail-nama').textContent = 'Detail: ' + nama;
    document.getElementById('tbody-detail').innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>';
    bukaModal('modal-detail');

    const resp = await fetch(`${BASE_URL}/superadmin/detail_absensi_user?id_user=${id_user}&bulan=${BULAN}&tahun=${TAHUN}`);
    const json = await resp.json();

    if (!json.data || json.data.length === 0) {
        document.getElementById('tbody-detail').innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi bulan ini.</td></tr>';
        return;
    }

    let rows = '';
    json.data.forEach(a => {
        const tgl = new Date(a.tanggal).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
        
        // Foto & Maps
        let buktiFoto = '';
        
        // Foto Masuk
        if (a.foto_masuk) {
            let aksiMasuk = (a.lat_masuk && a.lng_masuk) ? `window.open('https://www.google.com/maps?q=${a.lat_masuk},${a.lng_masuk}', '_blank')` : `previewFoto('${BASE_FOTO}${a.foto_masuk}')`;
            buktiFoto += `<button onclick="${aksiMasuk}" class="inline-block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:scale-110 transition-transform" title="Buka Lokasi Masuk">
                <img src="${BASE_FOTO}${a.foto_masuk}" class="w-full h-full object-cover">
            </button>`;
        }
        
        // Foto Pulang
        if (a.foto_pulang) {
            let aksiPulang = (a.lat_pulang && a.lng_pulang) ? `window.open('https://www.google.com/maps?q=${a.lat_pulang},${a.lng_pulang}', '_blank')` : `previewFoto('${BASE_FOTO}${a.foto_pulang}')`;
            buktiFoto += `<button onclick="${aksiPulang}" class="inline-block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:scale-110 transition-transform" title="Buka Lokasi Pulang">
                <img src="${BASE_FOTO}${a.foto_pulang}" class="w-full h-full object-cover">
            </button>`;
        }

        if (!buktiFoto) buktiFoto = '<span class="text-gray-300">—</span>';

        let editBtn = '';
        if (a.id_absensi) {
            editBtn = `<button onclick='bukaEdit(${JSON.stringify(a)})' class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 inline-flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-pen text-xs"></i>
                </button>`;
        } else {
            editBtn = `<span class="text-gray-300">—</span>`;
        }

        rows += `<tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-700">${tgl}</td>
            <td class="px-4 py-3 text-center">${statusBadge[a.status] || a.status}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-700">${a.jam_masuk || '—'}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-700">${a.jam_pulang || '—'}</td>
            <td class="px-4 py-3 text-center text-red-500 font-bold">${a.menit_terlambat > 0 ? a.menit_terlambat + ' m' : '—'}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex flex-row items-center justify-center gap-2">${buktiFoto}</div>
            </td>
            <td class="px-4 py-3 text-right print:hidden">
                ${editBtn}
            </td>
        </tr>`;
    });
    document.getElementById('tbody-detail').innerHTML = rows;
}

function bukaEdit(a) {
    document.getElementById('edit-id_absensi').value = a.id_absensi;
    document.getElementById('edit-status').value = a.status;
    document.getElementById('edit-jam_masuk').value = a.jam_masuk ? a.jam_masuk.substring(0,5) : '';
    document.getElementById('edit-jam_pulang').value = a.jam_pulang ? a.jam_pulang.substring(0,5) : '';
    toggleJamFields(a.status);
    bukaModal('modal-edit');
}

function toggleJamFields(status) {
    const fields = document.getElementById('jam-fields');
    fields.style.display = status === 'alfa' ? 'none' : 'block';
}

async function simpanEdit(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-simpan-edit');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.disabled = true;

    const resp = await fetch(`${BASE_URL}/superadmin/edit_absensi`, {
        method: 'POST', body: new FormData(document.getElementById('form-edit'))
    });
    const data = await resp.json();

    if (data.status === 'success') {
        tutupModal('modal-edit');
        // Reset button statenya agar tidak mutar terus saat dibuka lagi
        btn.innerHTML = 'Simpan';
        btn.disabled = false;
        
        // Reload detail modal
        const nama = document.getElementById('modal-detail-nama').textContent.replace('Detail: ', '');
        await lihatDetail(currentUserId, nama);
        
        // Update tabel utama (Summary) di balik layer via AJAX
        fetch(`${BASE_URL}/superadmin/ringkasan_absensi_user?id_user=${currentUserId}&bulan=${BULAN}&tahun=${TAHUN}`)
            .then(res => res.json())
            .then(resData => {
                if(resData.status === 'success') {
                    const row = document.getElementById('row-user-' + currentUserId);
                    if(row) {
                        const r = resData.data;
                        row.querySelector('.val-hadir').innerText = r.total_hadir;
                        
                        const elTelat = row.querySelector('.val-telat');
                        elTelat.innerText = r.total_telat;
                        elTelat.className = r.total_telat > 0 ? 'val-telat inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 font-bold' : 'val-telat inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-500 font-bold';
                        
                        row.querySelector('.val-menit').innerText = r.total_menit_terlambat || 0;
                        
                        const elAlfa = row.querySelector('.val-alfa');
                        elAlfa.innerText = r.total_alfa;
                        elAlfa.className = r.total_alfa > 0 ? 'val-alfa inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-700 font-bold' : 'val-alfa inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-500 font-bold';
                    }
                }
            });

        Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal', data.message, 'error');
        btn.innerHTML = 'Simpan';
        btn.disabled = false;
    }
}

function previewFoto(src) {
    document.getElementById('foto-preview').src = src;
    bukaModal('modal-foto');
}

function cetakDetailAbsensi() {
    const modalDetail = document.getElementById('modal-detail');
    const modalContent = modalDetail.querySelector('.bg-white');
    
    // Simpan class asli
    const originalBodyClasses = document.body.className;
    const originalModalClasses = modalDetail.className;
    const originalContentClasses = modalContent.className;
    
    // Tambahkan styling khusus print ke body untuk menyembunyikan elemen latar belakang
    const style = document.createElement('style');
    style.id = 'print-style';
    style.innerHTML = `
        @media print {
            body > *:not(#modal-detail) { display: none !important; }
            #modal-detail { position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: transparent; display: block !important; padding: 0 !important; }
            #modal-detail .bg-white { box-shadow: none !important; max-height: none !important; height: auto !important; width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
            #modal-detail .overflow-y-auto { overflow: visible !important; height: auto !important; }
            th.print\\:hidden, td.print\\:hidden { display: none !important; }
            .print\\:hidden { display: none !important; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            @page { margin: 1cm; size: portrait; }
        }
    `;
    document.head.appendChild(style);
    
    window.print();
    
    // Hapus styling khusus
    document.head.removeChild(style);
}

// Client-side pagination and filter logic
let rows = Array.from(document.querySelectorAll('#tbody-absensi tr')).filter(r => !r.classList.contains('no-data-row'));
let currentPage = 1;
const rowsPerPage = 10;
let filteredRows = [...rows];

function filterAndPaginate() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    
    filteredRows = rows.filter(r => {
        const nameNode = r.querySelector('.font-semibold.text-gray-800');
        if (!nameNode) return true;
        const name = nameNode.innerText.toLowerCase();
        return name.includes(q);
    });

    currentPage = 1;
    updateTable();
}

function updateTable() {
    const total = filteredRows.length;
    document.getElementById('totalRecords').innerText = `${total} Pegawai`;

    rows.forEach(r => r.style.display = 'none');

    let emptySearchRow = document.getElementById('empty-search-row');
    if (total === 0) {
        if (!emptySearchRow) {
            const tbody = document.getElementById('tbody-absensi');
            emptySearchRow = document.createElement('tr');
            emptySearchRow.id = 'empty-search-row';
            emptySearchRow.innerHTML = `<td colspan="7" class="px-6 py-10 text-center text-gray-400">Tidak ada pegawai yang cocok dengan pencarian.</td>`;
            tbody.appendChild(emptySearchRow);
        } else {
            emptySearchRow.style.display = '';
        }
        document.getElementById('paginationInfo').innerText = 'Menampilkan 0-0 dari 0 pegawai';
        document.getElementById('btnPrev').disabled = true;
        document.getElementById('btnNext').disabled = true;
        
        // Hide pagination container if zero total records
        document.getElementById('paginationControls').style.display = 'none';
        return;
    }

    if (emptySearchRow) emptySearchRow.style.display = 'none';
    document.getElementById('paginationControls').style.display = '';

    const start = (currentPage - 1) * rowsPerPage;
    const end = Math.min(start + rowsPerPage, total);

    for (let i = start; i < end; i++) {
        filteredRows[i].style.display = '';
    }

    document.getElementById('paginationInfo').innerText = `Menampilkan ${start + 1}-${end} dari ${total} pegawai`;
    document.getElementById('btnPrev').disabled = currentPage === 1;
    document.getElementById('btnNext').disabled = end >= total;
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        updateTable();
    }
}

function nextPage() {
    const total = filteredRows.length;
    if (currentPage * rowsPerPage < total) {
        currentPage++;
        updateTable();
    }
}

window.addEventListener('DOMContentLoaded', () => {
    filterAndPaginate();
});
</script>
