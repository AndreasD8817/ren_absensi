<?php
// Helper: daftar semua cabang untuk dropdown di dalam modal
$semua_cabang = $cabang ?? [];
$nama_bulan_list = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<style>
.select-modern {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.2em 1.2em;
    padding-right: 2.5rem !important;
}
</style>
<!-- Halaman Kelola Pegawai - Superadmin -->
<div class="flex h-screen overflow-hidden bg-gray-50">

    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <!-- Konten Utama -->
    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Pegawai</h2>
                <p class="text-gray-500 text-sm mt-1">Daftar seluruh pegawai terdaftar di sistem PT REN</p>
            </div>
            <div class="flex gap-2">
                <button onclick="bukaModalImport()" class="flex items-center gap-2 px-4 py-2.5 bg-green-100 text-green-700 text-sm font-bold rounded-xl hover:bg-green-200 active:scale-95 transition-all">
                    <i class="fa-solid fa-file-import"></i> Import CSV
                </button>
                <button onclick="bukaModalTambah()" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 hover:bg-blue-800 active:scale-95 transition-all">
                    <i class="fa-solid fa-plus"></i> Tambah Pegawai
                </button>
            </div>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="font-semibold text-gray-700">Total: <span class="text-primary font-bold" id="totalRecords"><?= count($pegawai) ?> Pegawai</span></p>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select id="filterCabang" onchange="filterAndPaginate()" class="select-modern px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary outline-none">
                        <option value="">Semua Cabang</option>
                        <?php foreach($semua_cabang as $c): ?>
                        <option value="<?= esc($c['nama_cabang']) ?>"><?= esc($c['nama_cabang']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="searchInput" onkeyup="filterAndPaginate()" placeholder="Cari NIP, nama..." class="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary outline-none w-full">
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tabelPegawai">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Pegawai</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">NIP</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Cabang</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Gaji Pokok</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-center px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="tbody-pegawai">
                        <?php foreach ($pegawai as $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['nama_lengkap']) ?>&size=40&background=random" class="w-9 h-9 rounded-full" alt="">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= esc($p['nama_lengkap']) ?></p>
                                        <p class="text-gray-400 text-xs"><?= esc($p['jabatan'] ?? '-') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600"><?= esc($p['nip']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($p['nama_cabang']) ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-700">Rp <?= number_format($p['gaji_pokok'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4">
                                <?php $rc = $p['role']==='superadmin'?'bg-purple-100 text-purple-700':($p['role']==='admin_cabang'?'bg-orange-100 text-orange-700':'bg-blue-100 text-blue-700'); ?>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $rc ?>"><?= ucfirst(str_replace('_',' ',$p['role'])) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($p['is_active']): ?>
                                    <span class="flex items-center gap-1.5 text-green-600 text-xs font-semibold"><span class="w-2 h-2 bg-green-500 rounded-full"></span>Aktif</span>
                                <?php else: ?>
                                    <span class="flex items-center gap-1.5 text-red-500 text-xs font-semibold"><span class="w-2 h-2 bg-red-400 rounded-full"></span>Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick='bukaModalEdit(<?= json_encode($p) ?>)' class="w-8 h-8 bg-blue-50 text-primary rounded-lg hover:bg-blue-100 transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button onclick="toggleStatus(<?= $p['id_user'] ?>, <?= $p['is_active'] ? 1 : 0 ?>)" class="w-8 h-8 <?= $p['is_active'] ? 'bg-orange-50 text-orange-500 hover:bg-orange-100' : 'bg-green-50 text-green-500 hover:bg-green-100' ?> rounded-lg transition-colors" title="<?= $p['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                        <i class="fa-solid <?= $p['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?> text-xs"></i>
                                    </button>
                                    <button onclick="hapusPegawai(<?= $p['id_user'] ?>, '<?= esc($p['nama_lengkap']) ?>')" class="w-8 h-8 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Hapus Permanen">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm" id="paginationControls">
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 font-medium">Tampilkan</span>
                    <select id="perPage" onchange="filterAndPaginate()" class="select-modern px-2 py-1 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-primary">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <span class="text-gray-500 font-medium" id="paginationInfo">dari 0 pegawai</span>
                </div>
                <div class="flex gap-2">
                    <button onclick="prevPage()" id="btnPrev" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 disabled:opacity-50 transition-colors">
                        Sebelumnya
                    </button>
                    <button onclick="nextPage()" id="btnNext" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-semibold hover:bg-gray-50 disabled:opacity-50 transition-colors">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ==================== MODAL IMPORT CSV ==================== -->
<div id="modal-import" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-file-import text-green-600 mr-2"></i>Import Pegawai via CSV</h3>
            <button onclick="tutupModal('modal-import')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-import" onsubmit="submitImport(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800">
                <p class="font-bold mb-1"><i class="fa-solid fa-circle-info mr-1"></i> Format File CSV:</p>
                <ul class="list-disc pl-5 space-y-1 mt-2 text-xs">
                    <li>Pastikan file berformat <b>.csv</b> (Comma Delimited).</li>
                    <li>Baris pertama (header) akan diabaikan.</li>
                    <li>Urutan kolom: <b>NIP, Nama Lengkap, Username, Password, Role (pegawai/admin_cabang), ID Cabang, Jabatan, Gaji Pokok, Tipe Lembur (Project/Non-Project), Tunj Jabatan, Tunj Transport, Tunj Makan, Tunj Kehadiran, Tunj Lainnya</b></li>
                </ul>
                <a href="<?= BASE_URL ?>/superadmin/download_template_pegawai" class="inline-block mt-3 px-3 py-1.5 bg-white border border-blue-200 text-blue-700 font-bold rounded-lg hover:bg-blue-50 transition-colors text-xs">
                    <i class="fa-solid fa-download mr-1"></i> Download Template CSV
                </a>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File CSV</label>
                <input type="file" name="file_csv" accept=".csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="tutupModal('modal-import')" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" id="btn-submit-import" class="px-5 py-2.5 text-sm bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-colors">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL TAMBAH ==================== -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-user-plus text-primary mr-2"></i>Tambah Pegawai Baru</h3>
            <button onclick="tutupModal('modal-tambah')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-tambah" onsubmit="submitTambah(event)" class="p-6 space-y-4">
    <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">NIP *</label>
                    <input name="nip" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Masukkan NIP">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Password * <span class="text-gray-400 font-normal">(min. 8 karakter)</span></label>
                    <input name="password" type="password" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="••••••••">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap *</label>
                <input name="nama_lengkap" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Nama sesuai KTP">
            </div>
            <div class="grid grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jabatan</label>
                    <input name="jabatan" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Contoh: Staff IT">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Pekerjaan *</label>
                    <select name="tipe_lembur" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <option value="Non-Project">Non-Project (Kantor)</option>
                        <option value="Project">Project (Lapangan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Role *</label>
                    <select name="role" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <option value="pegawai">Pegawai</option>
                        <option value="admin_cabang">Admin Cabang</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cabang *</label>
                    <select name="id_cabang" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <?php foreach ($semua_cabang as $c): ?>
                        <option value="<?= $c['id_cabang'] ?>"><?= esc($c['nama_cabang']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Gaji Pokok (Rp) *</label>
                    <input name="gaji_pokok" type="text" required class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4 mt-2">
                <h4 class="text-sm font-bold text-gray-700 mb-3">Tunjangan & Pajak</h4>
                <div class="grid grid-cols-4 gap-4 mb-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Pajak *</label>
                        <select name="status_pajak" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                            <option value="TK/0">TK/0</option><option value="TK/1">TK/1</option><option value="TK/2">TK/2</option><option value="TK/3">TK/3</option>
                            <option value="K/0">K/0</option><option value="K/1">K/1</option><option value="K/2">K/2</option><option value="K/3">K/3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" title="Pajak yang sudah dibayar tahun ini">Saldo PPh 21</label>
                        <input name="saldo_awal_pph21" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Jabatan</label>
                        <input name="tunj_jabatan" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Transport</label>
                        <input name="tunj_transportasi" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Makan</label>
                        <input name="tunj_makan" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Kehadiran</label>
                        <input name="tunj_kehadiran" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Lainnya</label>
                        <input name="tunj_lainnya" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-tambah')" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" id="btn-submit-tambah" class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold hover:bg-blue-800 transition-colors">Simpan Pegawai</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL EDIT ==================== -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-pen text-blue-500 mr-2"></i>Edit Data Pegawai</h3>
            <button onclick="tutupModal('modal-edit')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-edit" onsubmit="submitEdit(event)" class="p-6 space-y-4">
    <?= csrf_field() ?>
            <input type="hidden" name="id_user" id="edit-id_user">
            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">NIP *</label>
                    <input name="nip" id="edit-nip" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                    <input name="password" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="••••••••">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap *</label>
                <input name="nama_lengkap" id="edit-nama" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div class="grid grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jabatan</label>
                    <input name="jabatan" id="edit-jabatan" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Pekerjaan *</label>
                    <select name="tipe_lembur" id="edit-tipe_lembur" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <option value="Non-Project">Non-Project (Kantor)</option>
                        <option value="Project">Project (Lapangan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Role *</label>
                    <select name="role" id="edit-role" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <option value="pegawai">Pegawai</option>
                        <option value="admin_cabang">Admin Cabang</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cabang *</label>
                    <select name="id_cabang" id="edit-id_cabang" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <?php foreach ($semua_cabang as $c): ?>
                        <option value="<?= $c['id_cabang'] ?>"><?= esc($c['nama_cabang']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Gaji Pokok (Rp) *</label>
                    <input name="gaji_pokok" id="edit-gaji" type="text" required class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4 mt-2">
                <h4 class="text-sm font-bold text-gray-700 mb-3">Tunjangan & Pajak</h4>
                <div class="grid grid-cols-4 gap-4 mb-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Pajak *</label>
                        <select name="status_pajak" id="edit-status_pajak" required class="select-modern w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                            <option value="TK/0">TK/0</option><option value="TK/1">TK/1</option><option value="TK/2">TK/2</option><option value="TK/3">TK/3</option>
                            <option value="K/0">K/0</option><option value="K/1">K/1</option><option value="K/2">K/2</option><option value="K/3">K/3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" title="Pajak yang sudah dibayar tahun ini">Saldo PPh 21</label>
                        <input name="saldo_awal_pph21" id="edit-saldo_awal_pph21" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Jabatan</label>
                        <input name="tunj_jabatan" id="edit-tunj_jabatan" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Transport</label>
                        <input name="tunj_transportasi" id="edit-tunj_transportasi" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Makan</label>
                        <input name="tunj_makan" id="edit-tunj_makan" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Kehadiran</label>
                        <input name="tunj_kehadiran" id="edit-tunj_kehadiran" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Lainnya</label>
                        <input name="tunj_lainnya" id="edit-tunj_lainnya" type="text" class="currency-input w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Rp. 0">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-edit')" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold hover:bg-blue-800 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    let rows = Array.from(document.querySelectorAll('#tbody-pegawai tr')).filter(r => !r.classList.contains('no-data-row'));
    let currentPage = 1;
    let filteredRows = [...rows];

    // ================== CURRENCY FORMATTER ==================
    function formatRupiah(value) {
        if (value === null || value === undefined || value === '') return '';
        let number_string = value.toString().replace(/[^,\d]/g, ''),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah ? 'Rp. ' + rupiah : '';
    }

    function cleanRupiah(str) {
        return str.replace(/[^0-9]/g, '');
    }

    document.querySelectorAll('.currency-input').forEach(input => {
        // Format saat halaman dimuat
        if(input.value && input.value !== '0' && !input.value.startsWith('Rp')) {
            input.value = formatRupiah(input.value);
        }
        // Format saat mengetik
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    });
    // ========================================================

    function filterAndPaginate() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const cabang = document.getElementById('filterCabang').value.toLowerCase();
        
        filteredRows = rows.filter(r => {
            const textContent = r.innerText.toLowerCase();
            const matchesQuery = textContent.includes(q);
            const matchesCabang = cabang === '' || r.querySelector('td:nth-child(3)').innerText.toLowerCase().includes(cabang);
            return matchesQuery && matchesCabang;
        });

        currentPage = 1;
        updateTable();
    }

    function updateTable() {
        const total = filteredRows.length;
        document.getElementById('totalRecords').innerText = `${total} Pegawai`;

        rows.forEach(r => r.style.display = 'none');

        const rowsPerPage = parseInt(document.getElementById('perPage').value);
        const start = (currentPage - 1) * rowsPerPage;
        const end = Math.min(start + rowsPerPage, total);

        for (let i = start; i < end; i++) {
            filteredRows[i].style.display = '';
        }

        document.getElementById('paginationInfo').innerText = `dari ${total} pegawai`;
        document.getElementById('btnPrev').disabled = currentPage === 1 || total === 0;
        document.getElementById('btnNext').disabled = end >= total || total === 0;
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            updateTable();
        }
    }

    function nextPage() {
        const total = filteredRows.length;
        const rowsPerPage = parseInt(document.getElementById('perPage').value);
        if (currentPage * rowsPerPage < total) {
            currentPage++;
            updateTable();
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        if(rows.length > 0) filterAndPaginate();
    });

    async function hapusPegawai(id_user, nama) {
        const { isConfirmed } = await Swal.fire({
            title: 'Hapus Permanen?',
            html: `Anda yakin ingin menghapus <b>${nama}</b> secara permanen?<br><br><span class="text-sm text-red-600">Semua data absensi, foto wajah, pengajuan cuti, dan slip gaji akan dihapus permanen dari server dan tidak bisa dikembalikan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus Permanen!'
        });

        if (isConfirmed) {
            Swal.fire({ title: 'Menghapus...', text: 'Sedang membersihkan foto dan data', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const resp = await fetch('<?= BASE_URL ?>/superadmin/hapus_pegawai', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id_user, csrf_token: '<?= csrf_token() ?>' })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        }
    }

    function bukaModalTambah() {
        document.getElementById('form-tambah').reset();
        document.getElementById('modal-tambah').classList.remove('hidden');
        document.getElementById('modal-tambah').classList.add('flex');
    }

    function bukaModalImport() {
        document.getElementById('form-import').reset();
        document.getElementById('modal-import').classList.remove('hidden');
        document.getElementById('modal-import').classList.add('flex');
    }

    function bukaModalEdit(data) {
        document.getElementById('edit-id_user').value  = data.id_user;
        document.getElementById('edit-nip').value      = data.nip;
        document.getElementById('edit-nama').value     = data.nama_lengkap;
        document.getElementById('edit-jabatan').value  = data.jabatan;
        document.getElementById('edit-tipe_lembur').value = data.tipe_lembur || 'Non-Project';
        document.getElementById('edit-role').value     = data.role;
        document.getElementById('edit-id_cabang').value = data.id_cabang;
        
        document.getElementById('edit-gaji').value               = data.gaji_pokok ? formatRupiah(parseInt(data.gaji_pokok, 10)) : '';
        document.getElementById('edit-status_pajak').value       = data.status_pajak || 'TK/0';
        document.getElementById('edit-saldo_awal_pph21').value   = data.saldo_awal_pph21 ? formatRupiah(parseInt(data.saldo_awal_pph21, 10)) : '';
        document.getElementById('edit-tunj_jabatan').value       = data.tunj_jabatan ? formatRupiah(parseInt(data.tunj_jabatan, 10)) : '';
        document.getElementById('edit-tunj_transportasi').value  = data.tunj_transportasi ? formatRupiah(parseInt(data.tunj_transportasi, 10)) : '';
        document.getElementById('edit-tunj_makan').value         = data.tunj_makan ? formatRupiah(parseInt(data.tunj_makan, 10)) : '';
        document.getElementById('edit-tunj_kehadiran').value     = data.tunj_kehadiran ? formatRupiah(parseInt(data.tunj_kehadiran, 10)) : '';
        document.getElementById('edit-tunj_lainnya').value       = data.tunj_lainnya ? formatRupiah(parseInt(data.tunj_lainnya, 10)) : '';

        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('modal-edit').classList.add('flex');
    }

    function tutupModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    async function submitTambah(e) {
        e.preventDefault();
        const form = document.getElementById('form-tambah');
        
        // Bersihkan Rp dan titik sebelum disubmit
        form.querySelectorAll('.currency-input').forEach(input => {
            input.dataset.raw = input.value;
            input.value = cleanRupiah(input.value);
        });

        const btn = document.getElementById('btn-submit-tambah');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...';
        btn.disabled = true;
        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_pegawai', {
            method: 'POST', body: new FormData(form)
        });
        
        // Kembalikan value UI (opsional, in case error)
        form.querySelectorAll('.currency-input').forEach(input => {
            input.value = input.dataset.raw;
        });

        const data = await resp.json();
        btn.innerHTML = 'Simpan Pegawai'; btn.disabled = false;
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    async function submitImport(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-import');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengimpor...';
        btn.disabled = true;
        const resp = await fetch('<?= BASE_URL ?>/superadmin/import_pegawai_csv', {
            method: 'POST', body: new FormData(document.getElementById('form-import'))
        });
        const data = await resp.json();
        btn.innerHTML = 'Upload & Import'; btn.disabled = false;
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    async function submitEdit(e) {
        e.preventDefault();
        const form = document.getElementById('form-edit');
        
        // Bersihkan Rp dan titik sebelum disubmit
        form.querySelectorAll('.currency-input').forEach(input => {
            input.dataset.raw = input.value;
            input.value = cleanRupiah(input.value);
        });

        const resp = await fetch('<?= BASE_URL ?>/superadmin/update_pegawai', {
            method: 'POST', body: new FormData(form)
        });
        
        // Kembalikan value UI (opsional)
        form.querySelectorAll('.currency-input').forEach(input => {
            input.value = input.dataset.raw;
        });

        const data = await resp.json();
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    function toggleStatus(id, status_aktif) {
        const aksi = status_aktif ? 'Nonaktifkan' : 'Aktifkan';
        const icon = status_aktif ? 'warning' : 'question';
        Swal.fire({
            title: `${aksi} Pegawai?`, text: `Anda akan mengubah status akun pegawai ini.`,
            icon: icon, showCancelButton: true,
            confirmButtonText: `Ya, ${aksi}!`, cancelButtonText: 'Batal',
            confirmButtonColor: status_aktif ? '#d33' : '#22c55e'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const resp = await fetch('<?= BASE_URL ?>/superadmin/toggle_pegawai', {
                    method: 'POST', body: new URLSearchParams({ id_user: id, csrf_token: '<?= csrf_token() ?>' })
                });
                const data = await resp.json();
                if (data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            }
        });
    }
</script>
