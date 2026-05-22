<?php $nama_bulan_list = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; ?>
<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Bonus & THR</h2>
                <p class="text-gray-500 text-sm mt-1">Input data Bonus/THR untuk dimasukkan ke Slip Gaji secara otomatis.</p>
            </div>
            <div class="flex gap-2">
                <a href="<?= BASE_URL ?>/superadmin/download_template_bonus" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-file-excel text-green-600"></i> Download Template
                </a>
                <button onclick="bukaModalUpload()" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-green-700 transition-colors">
                    <i class="fa-solid fa-upload"></i> Upload CSV
                </button>
                <button onclick="bukaModalManual()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl shadow-md hover:bg-blue-800 transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Manual
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <form method="GET" class="flex gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                    <select name="bulan" class="w-40 px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:border-primary">
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= $nama_bulan_list[$i] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
                    <input type="number" name="tahun" value="<?= esc($tahun) ?>" class="w-32 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:border-primary">
                </div>
                <button type="submit" class="px-5 py-2 bg-gray-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-900 transition-colors">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-4 font-bold text-gray-500 uppercase text-xs">Pegawai</th>
                        <th class="text-left px-6 py-4 font-bold text-gray-500 uppercase text-xs">NIK / Cabang</th>
                        <th class="text-left px-6 py-4 font-bold text-gray-500 uppercase text-xs">Keterangan</th>
                        <th class="text-right px-6 py-4 font-bold text-gray-500 uppercase text-xs">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if(empty($list_bonus)): ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada data bonus/THR untuk periode ini.</td></tr>
                    <?php else: ?>
                        <?php foreach($list_bonus as $b): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-800"><?= esc($b['nama_lengkap']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($b['nip']) ?><br><span class="text-[11px] text-blue-600 font-bold"><?= esc($b['nama_cabang']) ?></span></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($b['keterangan']) ?></td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">+ Rp <?= number_format($b['nominal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Manual -->
<div id="modal-manual" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-plus text-primary mr-2"></i>Tambah Manual</h3>
            <button onclick="tutupModal('modal-manual')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-manual" onsubmit="submitManual(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="bulan" value="<?= esc($bulan) ?>">
            <input type="hidden" name="tahun" value="<?= esc($tahun) ?>">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Pegawai</label>
                <select name="id_user" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:border-primary">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach($semua_pegawai as $p): ?>
                    <option value="<?= $p['id_user'] ?>"><?= esc($p['nama_lengkap']) ?> (<?= esc($p['nip']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nominal (Rp)</label>
                <input type="number" name="nominal" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:border-primary" placeholder="Contoh: 1500000">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan</label>
                <input type="text" name="keterangan" required placeholder="Contoh: THR Lebaran 2026" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:border-primary">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="tutupModal('modal-manual')" class="px-5 py-2.5 text-sm border rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" id="btn-manual" class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold hover:bg-blue-800 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload -->
<div id="modal-upload" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-file-csv text-green-600 mr-2"></i>Upload CSV</h3>
            <button onclick="tutupModal('modal-upload')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-upload" onsubmit="submitUpload(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="bulan" value="<?= esc($bulan) ?>">
            <input type="hidden" name="tahun" value="<?= esc($tahun) ?>">
            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl text-xs border border-blue-100 leading-relaxed">
                Pastikan Anda sudah mendownload <b>Template Excel</b>, mengisinya, dan menyimpannya (Save As) dalam format <b>.CSV (Comma delimited)</b>.
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih File .CSV</label>
                <input type="file" name="file_csv" accept=".csv" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:border-primary bg-gray-50">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="tutupModal('modal-upload')" class="px-5 py-2.5 text-sm border rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" id="btn-upload" class="px-5 py-2.5 text-sm bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">Upload Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalManual() { document.getElementById('form-manual').reset(); document.getElementById('modal-manual').classList.replace('hidden', 'flex'); }
function bukaModalUpload() { document.getElementById('form-upload').reset(); document.getElementById('modal-upload').classList.replace('hidden', 'flex'); }
function tutupModal(id) { document.getElementById(id).classList.replace('flex', 'hidden'); }

async function submitManual(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-manual');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...'; btn.disabled = true;
    const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_bonus_manual', { method: 'POST', body: new FormData(e.target) });
    const data = await resp.json();
    if(data.status === 'success') { Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload()); }
    else { Swal.fire('Gagal', data.message, 'error'); btn.innerHTML = 'Simpan'; btn.disabled = false; }
}

async function submitUpload(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-upload');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengupload...'; btn.disabled = true;
    const resp = await fetch('<?= BASE_URL ?>/superadmin/upload_bonus_csv', { method: 'POST', body: new FormData(e.target) });
    const data = await resp.json();
    if(data.status === 'success') { Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload()); }
    else { Swal.fire('Gagal', data.message, 'error'); btn.innerHTML = 'Upload Data'; btn.disabled = false; }
}
</script>
