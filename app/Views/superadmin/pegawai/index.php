<?php
// Helper: daftar semua cabang untuk dropdown di dalam modal
$semua_cabang = $cabang ?? [];
$nama_bulan_list = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!-- Halaman Kelola Pegawai - Superadmin -->
<div class="flex h-screen overflow-hidden bg-gray-50">

    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <!-- Konten Utama -->
    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Pegawai</h2>
                <p class="text-gray-500 text-sm mt-1">Daftar seluruh pegawai terdaftar di sistem PT REN</p>
            </div>
            <button onclick="bukaModalTambah()" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 hover:bg-blue-800 active:scale-95 transition-all">
                <i class="fa-solid fa-plus"></i> Tambah Pegawai
            </button>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <p class="font-semibold text-gray-700">Total: <span class="text-primary font-bold"><?= count($pegawai) ?> Pegawai</span></p>
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="searchInput" onkeyup="filterTabel()" placeholder="Cari nama, NIP, jabatan..." class="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none w-64">
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
                                    <button onclick="toggleStatus(<?= $p['id_user'] ?>, <?= $p['is_active'] ? 1 : 0 ?>)" class="w-8 h-8 <?= $p['is_active'] ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-green-50 text-green-500 hover:bg-green-100' ?> rounded-lg transition-colors" title="<?= $p['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                        <i class="fa-solid <?= $p['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?> text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">NIP *</label>
                    <input name="nip" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Masukkan NIP">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Password *</label>
                    <input name="password" type="password" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Min. 8 karakter">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap *</label>
                <input name="nama_lengkap" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Nama sesuai KTP">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jabatan</label>
                    <input name="jabatan" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Jabatan / Posisi">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Role *</label>
                    <select name="role" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <option value="pegawai">Pegawai</option>
                        <option value="admin_cabang">Admin Cabang</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cabang *</label>
                    <select name="id_cabang" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <?php foreach ($semua_cabang as $c): ?>
                        <option value="<?= $c['id_cabang'] ?>"><?= esc($c['nama_cabang']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Gaji Pokok (Rp) *</label>
                    <input name="gaji_pokok" type="number" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Contoh: 3500000">
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4 mt-2">
                <h4 class="text-sm font-bold text-gray-700 mb-3">Tunjangan & Pajak</h4>
                <div class="grid grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Pajak *</label>
                        <select name="status_pajak" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                            <option value="TK/0">TK/0</option><option value="TK/1">TK/1</option><option value="TK/2">TK/2</option><option value="TK/3">TK/3</option>
                            <option value="K/0">K/0</option><option value="K/1">K/1</option><option value="K/2">K/2</option><option value="K/3">K/3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" title="Pajak yang sudah dibayar tahun ini">Saldo PPh 21</label>
                        <input name="saldo_awal_pph21" type="number" value="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Jabatan</label>
                        <input name="tunj_jabatan" type="number" value="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Transport</label>
                        <input name="tunj_transportasi" type="number" value="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Makan</label>
                        <input name="tunj_makan" type="number" value="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Kehadiran</label>
                        <input name="tunj_kehadiran" type="number" value="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Lainnya</label>
                        <input name="tunj_lainnya" type="number" value="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
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
            <div class="grid grid-cols-2 gap-4">
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jabatan</label>
                    <input name="jabatan" id="edit-jabatan" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Role *</label>
                    <select name="role" id="edit-role" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <option value="pegawai">Pegawai</option>
                        <option value="admin_cabang">Admin Cabang</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cabang *</label>
                    <select name="id_cabang" id="edit-id_cabang" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                        <?php foreach ($semua_cabang as $c): ?>
                        <option value="<?= $c['id_cabang'] ?>"><?= esc($c['nama_cabang']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Gaji Pokok (Rp) *</label>
                    <input name="gaji_pokok" id="edit-gaji" type="number" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4 mt-2">
                <h4 class="text-sm font-bold text-gray-700 mb-3">Tunjangan & Pajak</h4>
                <div class="grid grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Pajak *</label>
                        <select name="status_pajak" id="edit-status_pajak" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none bg-white">
                            <option value="TK/0">TK/0</option><option value="TK/1">TK/1</option><option value="TK/2">TK/2</option><option value="TK/3">TK/3</option>
                            <option value="K/0">K/0</option><option value="K/1">K/1</option><option value="K/2">K/2</option><option value="K/3">K/3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" title="Pajak yang sudah dibayar tahun ini">Saldo PPh 21</label>
                        <input name="saldo_awal_pph21" id="edit-saldo_awal_pph21" type="number" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Jabatan</label>
                        <input name="tunj_jabatan" id="edit-tunj_jabatan" type="number" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Transport</label>
                        <input name="tunj_transportasi" id="edit-tunj_transportasi" type="number" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Makan</label>
                        <input name="tunj_makan" id="edit-tunj_makan" type="number" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Kehadiran</label>
                        <input name="tunj_kehadiran" id="edit-tunj_kehadiran" type="number" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tunj. Lainnya</label>
                        <input name="tunj_lainnya" id="edit-tunj_lainnya" type="number" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none">
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
    function filterTabel() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#tbody-pegawai tr').forEach(r => {
            r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    function bukaModalTambah() {
        document.getElementById('form-tambah').reset();
        document.getElementById('modal-tambah').classList.remove('hidden');
        document.getElementById('modal-tambah').classList.add('flex');
    }

    function bukaModalEdit(data) {
        document.getElementById('edit-id_user').value  = data.id_user;
        document.getElementById('edit-nip').value      = data.nip;
        document.getElementById('edit-nama').value     = data.nama_lengkap;
        document.getElementById('edit-jabatan').value  = data.jabatan;
        document.getElementById('edit-role').value     = data.role;
        document.getElementById('edit-id_cabang').value = data.id_cabang;
        document.getElementById('edit-gaji').value     = data.gaji_pokok;
        
        document.getElementById('edit-status_pajak').value = data.status_pajak || 'TK/0';
        document.getElementById('edit-saldo_awal_pph21').value = data.saldo_awal_pph21 || 0;
        document.getElementById('edit-tunj_jabatan').value = data.tunj_jabatan || 0;
        document.getElementById('edit-tunj_transportasi').value  = data.tunj_transportasi || 0;
        document.getElementById('edit-tunj_makan').value         = data.tunj_makan || 0;
        document.getElementById('edit-tunj_kehadiran').value     = data.tunj_kehadiran || 0;
        document.getElementById('edit-tunj_lainnya').value       = data.tunj_lainnya || 0;

        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('modal-edit').classList.add('flex');
    }

    function tutupModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    async function submitTambah(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-tambah');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...';
        btn.disabled = true;
        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_pegawai', {
            method: 'POST', body: new FormData(document.getElementById('form-tambah'))
        });
        const data = await resp.json();
        btn.innerHTML = 'Simpan Pegawai'; btn.disabled = false;
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }

    async function submitEdit(e) {
        e.preventDefault();
        const resp = await fetch('<?= BASE_URL ?>/superadmin/update_pegawai', {
            method: 'POST', body: new FormData(document.getElementById('form-edit'))
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
