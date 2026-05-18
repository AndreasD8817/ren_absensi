<?php
$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$badge_colors = ['pending'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'];
$badge_labels = ['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak'];
?>
<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Cuti & Izin</h2>
                <p class="text-gray-500 text-sm mt-1">Semua pengajuan cuti dan izin dari seluruh cabang</p>
            </div>
            <!-- Filter Status -->
            <div class="flex items-center gap-2 bg-white border border-gray-100 rounded-2xl px-4 py-2 shadow-sm">
                <a href="<?= BASE_URL ?>/superadmin/cuti" class="px-3 py-1 text-xs font-bold rounded-lg <?= !$filter ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-100' ?> transition-colors">Semua</a>
                <a href="<?= BASE_URL ?>/superadmin/cuti?status=pending" class="px-3 py-1 text-xs font-bold rounded-lg <?= $filter === 'pending' ? 'bg-yellow-500 text-white' : 'text-gray-500 hover:bg-gray-100' ?> transition-colors">Menunggu</a>
                <a href="<?= BASE_URL ?>/superadmin/cuti?status=approved" class="px-3 py-1 text-xs font-bold rounded-lg <?= $filter === 'approved' ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100' ?> transition-colors">Disetujui</a>
                <a href="<?= BASE_URL ?>/superadmin/cuti?status=rejected" class="px-3 py-1 text-xs font-bold rounded-lg <?= $filter === 'rejected' ? 'bg-red-500 text-white' : 'text-gray-500 hover:bg-gray-100' ?> transition-colors">Ditolak</a>
            </div>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Pegawai</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Cabang</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($list_cuti)): ?>
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Tidak ada data pengajuan cuti.</td></tr>
                        <?php else: ?>
                            <?php foreach ($list_cuti as $c): ?>
                            <tr class="hover:bg-gray-50 transition-colors" id="row-cuti-<?= $c['id_cuti'] ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-100 text-primary font-bold flex items-center justify-center text-sm flex-shrink-0">
                                            <?= mb_substr($c['nama_lengkap'], 0, 2) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($c['nama_lengkap']) ?></p>
                                            <p class="text-xs text-gray-400"><?= $c['nip'] ?> · <?= $c['jabatan'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium"><?= htmlspecialchars($c['nama_cabang']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold"><?= $c['jenis'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-xs">
                                    <?= date('d M Y', strtotime($c['tanggal_mulai'])) ?> –
                                    <?= date('d M Y', strtotime($c['tanggal_selesai'])) ?>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs max-w-[200px] truncate"><?= htmlspecialchars($c['keterangan']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 <?= $badge_colors[$c['status']] ?> rounded-full text-xs font-bold">
                                        <?= $badge_labels[$c['status']] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($c['status'] === 'pending'): ?>
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="responCuti(<?= $c['id_cuti'] ?>, 'approved')" class="px-3 py-1.5 bg-green-100 text-green-700 hover:bg-green-200 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fa-solid fa-check mr-1"></i>Setujui
                                        </button>
                                        <button onclick="responCuti(<?= $c['id_cuti'] ?>, 'rejected')" class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fa-solid fa-xmark mr-1"></i>Tolak
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400">Sudah diproses</span>
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
async function responCuti(id, status) {
    const label = status === 'approved' ? 'Menyetujui' : 'Menolak';
    const conf = await Swal.fire({
        title: `${label} pengajuan ini?`,
        icon: status === 'approved' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: `Ya, ${label}!`,
        cancelButtonText: 'Batal',
        confirmButtonColor: status === 'approved' ? '#16a34a' : '#dc2626'
    });
    if (!conf.isConfirmed) return;

    const fd = new FormData();
    fd.append('id_cuti', id);
    fd.append('status', status);

    const resp = await fetch('<?= BASE_URL ?>/superadmin/respon_cuti_pusat', { method: 'POST', body: fd });
    const data = await resp.json();

    if (data.status === 'success') {
        // Update baris inline tanpa reload
        const row = document.getElementById(`row-cuti-${id}`);
        const badge = row.querySelector('td:nth-child(6) span');
        const aksiCell = row.querySelector('td:last-child');
        
        if (status === 'approved') {
            badge.className = 'px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold';
            badge.textContent = 'Disetujui';
        } else {
            badge.className = 'px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold';
            badge.textContent = 'Ditolak';
        }
        aksiCell.innerHTML = '<span class="text-xs text-gray-400">Sudah diproses</span>';
        Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal', data.message, 'error');
    }
}
</script>
