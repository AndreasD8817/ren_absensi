<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header & Tambah Tombol -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kalender Libur Nasional</h2>
                <p class="text-gray-500 text-sm mt-1">Kelola data hari libur nasional untuk pengecualian absensi (alfa)</p>
            </div>
            <div class="flex gap-2">
                <button onclick="bukaModalImport()" class="flex items-center gap-2 px-5 py-2.5 bg-green-100 text-green-700 text-sm font-semibold rounded-xl hover:bg-green-200 transition-colors">
                    <i class="fa-solid fa-file-import"></i> Import CSV
                </button>
                <button onclick="bukaModal()" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md hover:bg-blue-800 active:scale-95 transition-all">
                    <i class="fa-solid fa-plus"></i> Tambah Libur
                </button>
            </div>
        </div>

        <!-- Tabel Libur Nasional -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan Libur</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($libur)): ?>
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-400">Belum ada data libur nasional.</td></tr>
                        <?php else: ?>
                            <?php foreach ($libur as $l): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-800">
                                    <span class="inline-block px-3 py-1 bg-red-50 text-red-600 rounded-lg text-xs">
                                        <?= date('d M Y', strtotime($l['tanggal'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium"><?= esc($l['keterangan']) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick='editLibur(<?= json_encode($l) ?>)' class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button onclick="hapusLibur(<?= $l['id_libur'] ?>)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
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

<!-- Modal Form Libur -->
<div id="modal-libur" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800" id="modal-title">Tambah Hari Libur</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="form-libur" onsubmit="simpanLibur(event)" class="p-6">
    <?= csrf_field() ?>
            <input type="hidden" name="id_libur" id="id_libur">
            
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Libur <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="tanggal" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
            </div>
            
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan / Nama Libur <span class="text-red-500">*</span></label>
                <input type="text" name="keterangan" id="keterangan" required placeholder="Cth: Hari Raya Idul Fitri" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" id="btn-submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-md hover:bg-blue-800 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import CSV -->
<div id="modal-import" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="modal-import-content">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Import Data Libur (CSV)</h3>
            <button onclick="tutupModalImport()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="form-import" onsubmit="importCsv(event)" class="p-6">
    <?= csrf_field() ?>
            <div class="mb-4">
                <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded-lg border border-blue-100 mb-4">
                    <strong>Format CSV:</strong> Kolom 1 (Tanggal YYYY-MM-DD), Kolom 2 (Keterangan). Pastikan memiliki header di baris pertama.<br><br>
                    Contoh:<br>
                    <code>Tanggal,Keterangan</code><br>
                    <code>2026-08-17,Hari Kemerdekaan</code>
                    
                    <a href="<?= BASE_URL ?>/superadmin/download_template_libur" class="inline-block mt-3 px-3 py-1.5 bg-white border border-blue-200 text-blue-700 font-bold rounded-lg hover:bg-blue-50 transition-colors text-xs shadow-sm">
                        <i class="fa-solid fa-download mr-1"></i> Download Template CSV
                    </a>
                </div>
                
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pilih File CSV <span class="text-red-500">*</span></label>
                <input type="file" name="file_csv" id="file_csv" accept=".csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="tutupModalImport()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" id="btn-import" class="px-6 py-2.5 bg-green-600 text-white rounded-xl font-bold text-sm shadow-md hover:bg-green-700 transition-colors">Import CSV</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-libur');
    const content = document.getElementById('modal-content');
    const form = document.getElementById('form-libur');
    
    const modalImport = document.getElementById('modal-import');
    const contentImport = document.getElementById('modal-import-content');

    function bukaModal() {
        form.reset();
        document.getElementById('id_libur').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Hari Libur';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function editLibur(l) {
        document.getElementById('id_libur').value = l.id_libur;
        document.getElementById('tanggal').value = l.tanggal;
        document.getElementById('keterangan').value = l.keterangan;
        document.getElementById('modal-title').innerText = 'Edit Hari Libur';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModal() {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    function bukaModalImport() {
        document.getElementById('form-import').reset();
        modalImport.classList.remove('hidden');
        modalImport.classList.add('flex');
        setTimeout(() => {
            contentImport.classList.remove('scale-95', 'opacity-0');
            contentImport.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    function tutupModalImport() {
        contentImport.classList.remove('scale-100', 'opacity-100');
        contentImport.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modalImport.classList.remove('flex');
            modalImport.classList.add('hidden');
        }, 300);
    }

    async function simpanLibur(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_libur_nasional', {
            method: 'POST', body: new FormData(form)
        });
        const data = await resp.json();

        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.innerHTML = 'Simpan';
            btn.disabled = false;
        }
    }

    function hapusLibur(id) {
        Swal.fire({
            title: 'Hapus Hari Libur?',
            text: "Data ini tidak dapat dikembalikan!",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const resp = await fetch('<?= BASE_URL ?>/superadmin/hapus_libur_nasional/' + id, { method: 'POST', body: new URLSearchParams({ csrf_token: '<?= csrf_token() ?>' }) });
                const data = await resp.json();
                if (data.status === 'success') location.reload();
                else Swal.fire('Error', data.message, 'error');
            }
        });
    }

    async function importCsv(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-import');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengimpor...';
        btn.disabled = true;

        const formData = new FormData(document.getElementById('form-import'));
        const resp = await fetch('<?= BASE_URL ?>/superadmin/import_libur_nasional', {
            method: 'POST', body: formData
        });
        const data = await resp.json();

        if (data.status === 'success') {
            Swal.fire('Selesai', data.message, 'info').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.innerHTML = 'Import CSV';
            btn.disabled = false;
        }
    }
</script>
