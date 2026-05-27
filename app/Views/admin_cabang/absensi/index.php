<?php
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$base_foto = BASE_URL . '/uploads/';
?>
<!-- Halaman Absensi - Admin Cabang -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="flex h-screen overflow-hidden bg-gray-50">

    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header + Filter -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Data Absensi Pegawai</h2>
                <p class="text-gray-500 text-sm mt-1">Rekapitulasi kehadiran, keterlambatan, dan alfa bulanan</p>
            </div>
            <form method="GET" action="<?= BASE_URL ?>/admincabang/absensi" class="flex items-center gap-3 bg-white px-4 py-3 rounded-2xl shadow-sm border border-gray-100">
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
                            <tr class="hover:bg-gray-50 transition-colors">
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

                    </tr>
                </thead>
                <tbody id="tbody-detail" class="divide-y divide-gray-100">
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Modal Foto Preview -->
<div id="modal-foto" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80" onclick="if(event.target===this) tutupModal('modal-foto')">
    <img id="foto-preview" src="" class="max-h-[80vh] max-w-[90vw] rounded-2xl shadow-2xl object-contain">
</div>

<!-- Modal Lokasi & Foto (Leaflet) -->
<div id="modal-lokasi" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80 backdrop-blur-sm" onclick="if(event.target===this) tutupModal('modal-lokasi')">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800 text-sm"><i class="fa-solid fa-map-location-dot text-blue-500 mr-2"></i>Bukti & Lokasi Absen</h3>
            <button onclick="tutupModal('modal-lokasi')" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div class="p-5 flex flex-col gap-4">
            <img id="lokasi-foto" src="" class="w-full h-56 object-cover rounded-2xl border border-gray-200 shadow-inner bg-gray-100">
            <div id="lokasi-map" class="w-full h-56 rounded-2xl border border-gray-200 shadow-inner z-0 relative"></div>
            <a id="btn-gmaps" href="#" target="_blank" class="w-full py-3 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold text-sm rounded-xl text-center transition-colors shadow-sm">
                <i class="fa-solid fa-location-arrow mr-1"></i> Buka Titik Ini di Google Maps
            </a>
        </div>
    </div>
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

let leafletMap = null;
let leafletMarker = null;

function bukaLokasi(lat, lng, urlFoto) {
    document.getElementById('lokasi-foto').src = urlFoto;
    document.getElementById('btn-gmaps').href = `https://www.google.com/maps?q=${lat},${lng}`;
    
    bukaModal('modal-lokasi');
    
    setTimeout(() => {
        if (!leafletMap) {
            leafletMap = L.map('lokasi-map').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(leafletMap);
            leafletMarker = L.marker([lat, lng]).addTo(leafletMap);
        } else {
            leafletMap.invalidateSize();
            leafletMap.setView([lat, lng], 16);
            leafletMarker.setLatLng([lat, lng]);
        }
    }, 300);
}

async function lihatDetail(id_user, nama) {
    currentUserId = id_user;
    document.getElementById('modal-detail-nama').textContent = 'Detail: ' + nama;
    document.getElementById('tbody-detail').innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>';
    bukaModal('modal-detail');

    const resp = await fetch(`${BASE_URL}/admincabang/detail_absensi_user?id_user=${id_user}&bulan=${BULAN}&tahun=${TAHUN}`);
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
            let aksiMasuk = (a.lat_masuk && a.lng_masuk) ? `bukaLokasi(${a.lat_masuk}, ${a.lng_masuk}, '${BASE_FOTO}${a.foto_masuk}')` : `previewFoto('${BASE_FOTO}${a.foto_masuk}')`;
            buktiFoto += `<button onclick="${aksiMasuk}" class="inline-block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:scale-110 transition-transform" title="Buka Bukti Masuk">
                <img src="${BASE_FOTO}${a.foto_masuk}" class="w-full h-full object-cover">
            </button>`;
        }
        
        // Foto Pulang
        if (a.foto_pulang) {
            let aksiPulang = (a.lat_pulang && a.lng_pulang) ? `bukaLokasi(${a.lat_pulang}, ${a.lng_pulang}, '${BASE_FOTO}${a.foto_pulang}')` : `previewFoto('${BASE_FOTO}${a.foto_pulang}')`;
            buktiFoto += `<button onclick="${aksiPulang}" class="inline-block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:scale-110 transition-transform" title="Buka Bukti Pulang">
                <img src="${BASE_FOTO}${a.foto_pulang}" class="w-full h-full object-cover">
            </button>`;
        }

        if (!buktiFoto) buktiFoto = '<span class="text-gray-300">—</span>';

        rows += `<tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-700">${tgl}</td>
            <td class="px-4 py-3 text-center">${statusBadge[a.status] || a.status}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-700">${a.jam_masuk || '—'}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-700">${a.jam_pulang || '—'}</td>
            <td class="px-4 py-3 text-center text-red-500 font-bold">${a.menit_terlambat > 0 ? a.menit_terlambat + ' m' : '—'}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex flex-row items-center justify-center gap-2">${buktiFoto}</div>
            </td>
        </tr>`;
    });
    document.getElementById('tbody-detail').innerHTML = rows;
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
