<?php
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$base_foto = BASE_URL . '/uploads/';
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
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
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($ringkasan)): ?>
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Tidak ada data pegawai aktif atau absensi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ringkasan as $r): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($r['nama_lengkap']) ?>&size=36&background=random" class="w-9 h-9 rounded-full" alt="">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($r['nama_lengkap']) ?></p>
                                            <p class="text-gray-400 text-xs"><?= htmlspecialchars($r['nip']) ?> - <?= htmlspecialchars($r['jabatan']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium"><?= htmlspecialchars($r['nama_cabang']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 font-bold"><?= $r['total_hadir'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full <?= $r['total_telat'] > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-50 text-gray-500' ?> font-bold">
                                        <?= $r['total_telat'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-red-500"><?= $r['total_menit_terlambat'] ?? 0 ?> m</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full <?= $r['total_alfa'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-500' ?> font-bold">
                                        <?= $r['total_alfa'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="lihatDetail(<?= $r['id_user'] ?>, '<?= htmlspecialchars($r['nama_lengkap']) ?>')" 
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
            <button onclick="tutupModal('modal-detail')" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
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
                        <th class="text-right px-4 py-3 font-bold text-gray-500 uppercase">Edit</th>
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
        
        // Foto & Maps masuk
        let buktiFoto = '';
        if (a.foto_masuk) {
            buktiFoto += `<button onclick="previewFoto('${BASE_FOTO}${a.foto_masuk}')" class="inline-block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 mr-1 hover:scale-110 transition-transform" title="Foto Masuk">
                <img src="${BASE_FOTO}${a.foto_masuk}" class="w-full h-full object-cover">
            </button>`;
        }
        if (a.lat_masuk && a.lng_masuk) {
            buktiFoto += `<a href="https://www.google.com/maps?q=${a.lat_masuk},${a.lng_masuk}" target="_blank" title="Lokasi Masuk" class="inline-flex w-8 h-8 rounded-lg bg-green-100 text-green-700 items-center justify-center hover:bg-green-200 transition-colors mr-1">
                <i class="fa-solid fa-map-pin text-xs"></i>
            </a>`;
        }
        if (a.foto_pulang) {
            buktiFoto += `<button onclick="previewFoto('${BASE_FOTO}${a.foto_pulang}')" class="inline-block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 mr-1 hover:scale-110 transition-transform" title="Foto Pulang">
                <img src="${BASE_FOTO}${a.foto_pulang}" class="w-full h-full object-cover opacity-80">
            </button>`;
        }
        if (a.lat_pulang && a.lng_pulang) {
            buktiFoto += `<a href="https://www.google.com/maps?q=${a.lat_pulang},${a.lng_pulang}" target="_blank" title="Lokasi Pulang" class="inline-flex w-8 h-8 rounded-lg bg-blue-100 text-blue-700 items-center justify-center hover:bg-blue-200 transition-colors">
                <i class="fa-solid fa-map-pin text-xs"></i>
            </a>`;
        }
        if (!buktiFoto) buktiFoto = '<span class="text-gray-300">—</span>';

        rows += `<tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-700">${tgl}</td>
            <td class="px-4 py-3 text-center">${statusBadge[a.status] || a.status}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-700">${a.jam_masuk || '—'}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-700">${a.jam_pulang || '—'}</td>
            <td class="px-4 py-3 text-center text-red-500 font-bold">${a.menit_terlambat > 0 ? a.menit_terlambat + ' m' : '—'}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1 flex-wrap">${buktiFoto}</div>
            </td>
            <td class="px-4 py-3 text-right">
                <button onclick='bukaEdit(${JSON.stringify(a)})' class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 inline-flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-pen text-xs"></i>
                </button>
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
        // Reload detail modal
        const nama = document.getElementById('modal-detail-nama').textContent.replace('Detail: ', '');
        await lihatDetail(currentUserId, nama);
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
</script>
