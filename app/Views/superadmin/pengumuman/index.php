<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header & Tambah Tombol -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Pengumuman</h2>
                <p class="text-gray-500 text-sm mt-1">Broadcast informasi ke seluruh dashboard pegawai</p>
            </div>
            <button onclick="bukaModal()" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md hover:bg-blue-800 active:scale-95 transition-all">
                <i class="fa-solid fa-plus"></i> Buat Pengumuman Baru
            </button>
        </div>

        <!-- Tabel Pengumuman -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Pengumuman</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Isi Informasi</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tgl Dibuat</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($pengumuman)): ?>
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada pengumuman yang dibuat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pengumuman as $p): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-800"><?= esc($p['judul']) ?></td>
                                <td class="px-6 py-4 text-gray-600 line-clamp-2 max-w-sm"><?= esc($p['isi']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($p['is_active']): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded-full border border-green-200">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-500 text-[11px] font-bold rounded-full border border-gray-200">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick='editPengumuman(<?= json_encode($p) ?>)' class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button onclick="hapusPengumuman(<?= $p['id_pengumuman'] ?>)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
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

<!-- Modal Form Pengumuman -->
<div id="modal-pengumuman" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="modal-content">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800" id="modal-title">Buat Pengumuman Baru</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="form-pengumuman" onsubmit="simpanPengumuman(event)" class="p-6">
    <?= csrf_field() ?>
            <input type="hidden" name="id_pengumuman" id="id_pengumuman">
            
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Pengumuman <span class="text-red-500">*</span></label>
                <input type="text" name="judul" id="judul" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
            </div>
            
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Isi Informasi <span class="text-red-500">*</span></label>
                <textarea name="isi" id="isi" rows="4" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700"></textarea>
            </div>
            
            <div class="mb-6 flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-5 h-5 text-primary rounded border-gray-300 focus:ring-primary">
                <label for="is_active" class="text-sm font-medium text-gray-700">Tampilkan Pengumuman (Aktif)</label>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" id="btn-submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-md hover:bg-blue-800 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-pengumuman');
    const content = document.getElementById('modal-content');
    const form = document.getElementById('form-pengumuman');

    function bukaModal() {
        form.reset();
        document.getElementById('id_pengumuman').value = '';
        document.getElementById('modal-title').innerText = 'Buat Pengumuman Baru';
        document.getElementById('is_active').checked = true;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function editPengumuman(p) {
        document.getElementById('id_pengumuman').value = p.id_pengumuman;
        document.getElementById('judul').value = p.judul;
        document.getElementById('isi').value = p.isi;
        document.getElementById('is_active').checked = p.is_active == 1;
        document.getElementById('modal-title').innerText = 'Edit Pengumuman';
        
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

    async function simpanPengumuman(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_pengumuman', {
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

    function hapusPengumuman(id) {
        Swal.fire({
            title: 'Hapus Pengumuman?',
            text: "Data ini tidak dapat dikembalikan!",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const resp = await fetch('<?= BASE_URL ?>/superadmin/hapus_pengumuman/' + id, { 
                    method: 'POST', body: new URLSearchParams({ csrf_token: '<?= csrf_token() ?>' }) 
                });
                const data = await resp.json();
                if (data.status === 'success') location.reload();
                else Swal.fire('Error', data.message, 'error');
            }
        });
    }
</script>
