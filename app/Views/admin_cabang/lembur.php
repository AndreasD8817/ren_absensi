<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Persetujuan Lembur</h2>
                <p class="text-gray-500 text-sm mt-1">Daftar pengajuan lembur/overtime dari pegawai cabang Anda.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Pegawai</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Lembur</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($list_lembur)): ?>
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Tidak ada pengajuan lembur.</td></tr>
                        <?php else: ?>
                            <?php foreach ($list_lembur as $l): 
                                $tgl = date('d M Y', strtotime($l['tanggal']));
                                $jam = date('H:i', strtotime($l['jam_mulai'])) . ' - ' . date('H:i', strtotime($l['jam_selesai']));
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($l['nama_lengkap']) ?>&background=random" class="w-8 h-8 rounded-full">
                                        <div>
                                            <p class="font-bold text-gray-800"><?= esc($l['nama_lengkap']) ?></p>
                                            <p class="text-xs text-gray-500"><?= esc($l['jabatan']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800 text-xs"><?= $tgl ?></p>
                                    <p class="text-[11px] text-gray-500 mt-0.5"><i class="fa-regular fa-clock"></i> <?= $jam ?> (<?= $l['durasi_jam'] ?> Jam)</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-gray-600 line-clamp-2 max-w-xs"><?= esc($l['keterangan']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($l['status'] == 'pending'): ?>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Menunggu</span>
                                    <?php elseif ($l['status'] == 'approved'): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Disetujui</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($l['status'] == 'pending'): ?>
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="prosesLembur(<?= $l['id_lembur'] ?>, 'approved')" class="px-3 py-1.5 bg-green-500 text-white text-xs font-bold rounded hover:bg-green-600 transition-colors">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button onclick="prosesLembur(<?= $l['id_lembur'] ?>, 'rejected')" class="px-3 py-1.5 bg-red-500 text-white text-xs font-bold rounded hover:bg-red-600 transition-colors">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Selesai</span>
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
    async function prosesLembur(id_lembur, status) {
        let alasan = '';
        if (status === 'rejected') {
            const { value: inputAlasan } = await Swal.fire({
                title: 'Alasan Penolakan',
                input: 'text',
                inputPlaceholder: 'Tulis alasan kenapa lembur ditolak...',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) return 'Alasan harus diisi!'
                }
            });
            if (!inputAlasan) return;
            alasan = inputAlasan;
        } else {
            const confirm = await Swal.fire({
                title: 'Setujui Lembur?',
                text: "Jam lembur ini akan masuk ke perhitungan gaji.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Ya, Setujui'
            });
            if (!confirm.isConfirmed) return;
        }

        const fd = new FormData();
        fd.append('id_lembur', id_lembur);
        fd.append('status', status);
        if (alasan) fd.append('alasan_reject', alasan);
        fd.append('csrf_token', '<?= csrf_token() ?>');

        const resp = await fetch('<?= BASE_URL ?>/admincabang/respon_lembur', {
            method: 'POST', body: fd
        });
        const data = await resp.json();

        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }
</script>
