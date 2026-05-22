<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Monitoring Kas Denda</h2>
                <p class="text-gray-500 text-sm mt-1">Pantau total denda dari pegawai cabang ini dan riwayat pencairan dari Pusat.</p>
            </div>
        </div>

        <!-- Kartu Saldo -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-3xl p-8 text-white shadow-lg mb-8 relative overflow-hidden max-w-lg">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
            <p class="text-blue-200 text-sm font-semibold uppercase tracking-wider mb-2">Total Kas Tersedia di Pusat</p>
            <h3 class="text-4xl font-black mb-6">Rp <?= number_format($rekap['saldo'] ?? 0, 0, ',', '.') ?></h3>
            <div class="flex gap-8">
                <div>
                    <p class="text-blue-300 text-xs font-semibold mb-1">Total Terkumpul (Dari Gaji)</p>
                    <p class="font-bold text-green-300">+ Rp <?= number_format($rekap['total_pemasukan'] ?? 0, 0, ',', '.') ?></p>
                </div>
                <div>
                    <p class="text-blue-300 text-xs font-semibold mb-1">Total Dicairkan Pusat</p>
                    <p class="font-bold text-red-300">- Rp <?= number_format($rekap['total_pengeluaran'] ?? 0, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Riwayat Transaksi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-700"><i class="fa-solid fa-clock-rotate-left mr-2"></i>Riwayat Kas Cabang</h3>
            </div>
            <div class="p-0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 uppercase">Jenis</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-gray-500 uppercase">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($riwayat)): ?>
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada perputaran kas denda.</td></tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $trx): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-600"><?= date('d M Y', strtotime($trx['tanggal'])) ?></td>
                                <td class="px-6 py-4 font-medium text-gray-800"><?= esc($trx['keterangan']) ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($trx['jenis'] == 'pemasukan'): ?>
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded-full">Pemasukan (Dari Gaji)</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 bg-red-100 text-red-600 text-[11px] font-bold rounded-full">Pengeluaran (Dicairkan Pusat)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right font-bold <?= $trx['jenis'] == 'pemasukan' ? 'text-green-600' : 'text-red-500' ?>">
                                    <?= $trx['jenis'] == 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($trx['nominal'], 0, ',', '.') ?>
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
