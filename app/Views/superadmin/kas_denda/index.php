<?php $nama_bulan_list = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; ?>
<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kas Denda Cabang</h2>
                <p class="text-gray-500 text-sm mt-1">Monitoring dan kelola uang denda keterlambatan dari seluruh cabang.</p>
            </div>
            <button onclick="bukaModalTransaksi()" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md hover:bg-blue-800 transition-colors">
                <i class="fa-solid fa-plus"></i> Catat Pengeluaran
            </button>
        </div>

        <!-- Rekap Saldo per Cabang -->
        <h3 class="text-lg font-bold text-gray-700 mb-4">Rekap Saldo Tersimpan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php foreach ($rekap as $r): ?>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-blue-100 to-transparent rounded-bl-3xl"></div>
                <div>
                    <h4 class="text-gray-500 text-sm font-semibold mb-1"><?= htmlspecialchars($r['nama_cabang']) ?></h4>
                    <p class="text-3xl font-bold text-gray-800">Rp <?= number_format($r['saldo'], 0, ',', '.') ?></p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between text-xs">
                    <span class="text-green-600 font-semibold"><i class="fa-solid fa-arrow-down mr-1"></i> In: Rp <?= number_format($r['total_pemasukan'], 0, ',', '.') ?></span>
                    <span class="text-red-500 font-semibold"><i class="fa-solid fa-arrow-up mr-1"></i> Out: Rp <?= number_format($r['total_pengeluaran'], 0, ',', '.') ?></span>
                </div>
                <div class="mt-3">
                    <a href="?id_cabang=<?= $r['id_cabang'] ?>" class="block text-center text-xs font-bold text-blue-600 bg-blue-50 py-2 rounded-lg hover:bg-blue-600 hover:text-white transition-colors">Lihat Riwayat</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Riwayat Transaksi -->
        <?php if ($cabang_terpilih): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-700">Riwayat Transaksi Cabang</h3>
                <a href="?" class="text-sm text-gray-500 hover:text-gray-800"><i class="fa-solid fa-xmark mr-1"></i> Tutup Filter</a>
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
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada riwayat transaksi</td></tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $trx): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-600"><?= date('d M Y', strtotime($trx['tanggal'])) ?></td>
                                <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($trx['keterangan']) ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($trx['jenis'] == 'pemasukan'): ?>
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded-full">Pemasukan (Dari Gaji)</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 bg-red-100 text-red-600 text-[11px] font-bold rounded-full">Pengeluaran (Kas Keluar)</span>
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
        <?php endif; ?>
    </main>
</div>

<!-- Modal Catat Transaksi Pengeluaran -->
<div id="modal-transaksi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-money-bill-transfer text-primary mr-2"></i>Catat Pengeluaran Kas</h3>
            <button onclick="tutupModal('modal-transaksi')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-transaksi" onsubmit="submitTransaksi(event)" class="p-6 space-y-4">
            <input type="hidden" name="jenis" value="pengeluaran">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal</label>
                <input name="tanggal" type="date" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Cabang</label>
                <select name="id_cabang" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm outline-none bg-white focus:border-primary">
                    <option value="">-- Pilih Cabang --</option>
                    <?php foreach ($cabang as $c): ?>
                    <option value="<?= $c['id_cabang'] ?>"><?= htmlspecialchars($c['nama_cabang']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nominal (Rp)</label>
                <input name="nominal" type="number" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm outline-none focus:border-primary" placeholder="Contoh: 150000">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan / Tujuan Pencairan</label>
                <textarea name="keterangan" required rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm outline-none focus:border-primary" placeholder="Makan bersama tim cabang..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="tutupModal('modal-transaksi')" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" id="btn-submit" class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold hover:bg-blue-800 transition-colors">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalTransaksi() {
        document.getElementById('form-transaksi').reset();
        document.getElementById('modal-transaksi').classList.remove('hidden');
        document.getElementById('modal-transaksi').classList.add('flex');
    }

    function tutupModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    async function submitTransaksi(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_transaksi_kas', {
            method: 'POST', body: new FormData(document.getElementById('form-transaksi'))
        });
        const data = await resp.json();
        
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.innerHTML = 'Simpan Pengeluaran';
            btn.disabled = false;
        }
    }
</script>
