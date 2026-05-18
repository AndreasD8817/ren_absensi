<div class="flex h-screen overflow-hidden bg-orange-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header & Tambah Tombol -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Libur Lokal Cabang</h2>
                <p class="text-gray-500 text-sm mt-1">Kelola hari libur spesifik untuk cabang ini (Cth: Libur Keagamaan Lokal)</p>
            </div>
            <button onclick="bukaModal()" class="flex items-center gap-2 px-6 py-2.5 bg-orange-600 text-white text-sm font-semibold rounded-xl shadow-md hover:bg-orange-700 active:scale-95 transition-all">
                <i class="fa-solid fa-plus"></i> Tambah Libur Lokal
            </button>
        </div>

        <!-- Tabel Libur Lokal -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status Override</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($libur)): ?>
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">Belum ada data libur lokal.</td></tr>
                        <?php else: ?>
                            <?php foreach ($libur as $l): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-800">
                                    <span class="inline-block px-3 py-1 <?= $l['status'] === 'libur_lokal' ? 'bg-orange-50 text-orange-600' : 'bg-red-50 text-red-600' ?> rounded-lg text-xs">
                                        <?= date('d M Y', strtotime($l['tanggal'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium"><?= htmlspecialchars($l['keterangan']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($l['status'] === 'libur_lokal'): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded-full">Libur Lokal (Dikecualikan Alfa)</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-700 text-[11px] font-bold rounded-full">Tetap Masuk (Paksa)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick='editLibur(<?= json_encode($l) ?>)' class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button onclick="hapusLibur(<?= $l['id_override'] ?>)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
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

<!-- Modal Form Libur Lokal -->
<div id="modal-libur" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800" id="modal-title">Tambah Libur Lokal</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="form-libur" onsubmit="simpanLibur(event)" class="p-6">
            <input type="hidden" name="id_override" id="id_override">
            
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="tanggal" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-gray-700">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-gray-700">
                    <option value="libur_lokal">Libur Lokal (Pegawai Cabang Libur)</option>
                    <option value="tetap_masuk">Tetap Masuk (Meskipun Libur Nasional)</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan <span class="text-red-500">*</span></label>
                <input type="text" name="keterangan" id="keterangan" required placeholder="Cth: Hari Raya Galungan" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-gray-700">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" id="btn-submit" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl font-bold text-sm shadow-md hover:bg-orange-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-libur');
    const content = document.getElementById('modal-content');
    const form = document.getElementById('form-libur');

    function bukaModal() {
        form.reset();
        document.getElementById('id_override').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Libur Lokal';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function editLibur(l) {
        document.getElementById('id_override').value = l.id_override;
        document.getElementById('tanggal').value = l.tanggal;
        document.getElementById('status').value = l.status;
        document.getElementById('keterangan').value = l.keterangan;
        document.getElementById('modal-title').innerText = 'Edit Libur Lokal';
        
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

    async function simpanLibur(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/admincabang/simpan_libur_lokal', {
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
                const resp = await fetch('<?= BASE_URL ?>/admincabang/hapus_libur_lokal/' + id, { method: 'POST' });
                const data = await resp.json();
                if (data.status === 'success') location.reload();
                else Swal.fire('Error', data.message, 'error');
            }
        });
    }
</script>
