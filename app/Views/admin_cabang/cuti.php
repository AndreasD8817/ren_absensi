<!-- Halaman Persetujuan Cuti - Admin Cabang -->
<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <header class="mb-8 border-b border-gray-200 pb-4">
            <h2 class="text-2xl font-bold text-gray-800">Persetujuan Cuti & Izin</h2>
            <p class="text-gray-500 text-sm mt-1">Kelola pengajuan cuti, sakit, dan izin pegawai di cabang Anda</p>
        </header>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase">Pegawai</th>
                            <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase">Pengajuan</th>
                            <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase">Foto/Bukti</th>
                            <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($list_cuti)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p>Belum ada pengajuan cuti/izin.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($list_cuti as $c): 
                                $bg_icon = 'bg-blue-100 text-blue-600';
                                if ($c['jenis'] == 'Sakit') $bg_icon = 'bg-red-100 text-red-500';
                                elseif ($c['jenis'] == 'Izin') $bg_icon = 'bg-orange-100 text-orange-500';
                            ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($c['nama_lengkap']) ?></p>
                                    <p class="text-xs text-gray-500"><?= $c['nip'] ?> &bull; <?= $c['jabatan'] ?></p>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $bg_icon ?>"><?= $c['jenis'] ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-gray-700"><?= date('d M Y', strtotime($c['tanggal_mulai'])) ?></p>
                                    <?php if($c['tanggal_mulai'] != $c['tanggal_selesai']): ?>
                                    <p class="text-xs text-gray-500">s/d <?= date('d M Y', strtotime($c['tanggal_selesai'])) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-gray-600 max-w-[200px] truncate" title="<?= htmlspecialchars($c['keterangan']) ?>">
                                        <?= htmlspecialchars($c['keterangan']) ?>
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <?php if($c['bukti_foto']): ?>
                                    <a href="<?= BASE_URL ?>/uploads/<?= $c['bukti_foto'] ?>" target="_blank" class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-xs font-semibold bg-blue-50 px-3 py-1.5 rounded-lg w-max">
                                        <i class="fa-solid fa-image"></i> Lihat
                                    </a>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Tidak ada bukti</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if($c['status'] == 'pending'): ?>
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="responCuti(<?= $c['id_cuti'] ?>, 'approved')" class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-500 hover:text-white transition-colors" title="Setujui">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button onclick="responCuti(<?= $c['id_cuti'] ?>, 'rejected')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-colors" title="Tolak">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <?php elseif($c['status'] == 'approved'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full"><i class="fa-solid fa-check-circle mr-1"></i>Disetujui</span>
                                    <?php else: ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full"><i class="fa-solid fa-times-circle mr-1"></i>Ditolak</span>
                                    <?php endif; ?>
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

<script>
    async function responCuti(id_cuti, status) {
        const action = status === 'approved' ? 'Menyetujui' : 'Menolak';
        const color = status === 'approved' ? '#22c55e' : '#ef4444';

        const { isConfirmed } = await Swal.fire({
            title: action + ' Pengajuan?',
            text: `Apakah Anda yakin ingin ${action.toLowerCase()} pengajuan ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor: '#9ca3af',
            confirmButtonText: `Ya, ${action}`,
            cancelButtonText: 'Batal'
        });

        if (!isConfirmed) return;

        const resp = await fetch('<?= BASE_URL ?>/admincabang/respon_cuti', {
            method: 'POST',
            body: new URLSearchParams({ id_cuti, status })
        });
        const data = await resp.json();

        if (data.status === 'success') {
            Swal.fire({
                title: 'Berhasil!', text: data.message, icon: 'success', confirmButtonColor: '#7c2d12'
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }
</script>
