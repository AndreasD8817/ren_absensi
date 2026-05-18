<!-- Dashboard Admin Cabang - Data Nyata -->
<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <header class="mb-8 flex justify-between items-end border-b border-gray-200 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Dashboard <?= htmlspecialchars($cabang['nama_cabang']) ?></h2>
                <p class="text-gray-500 text-sm mt-1">Ringkasan aktivitas absensi cabang hari ini</p>
            </div>
            <p class="text-sm font-semibold text-gray-600"><?= date('l, d F Y') ?></p>
        </header>

        <?php $s = $statistik; ?>
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-green-500 relative overflow-hidden group">
                <div class="absolute right-[-10px] top-[10px] text-green-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-check-circle text-8xl"></i></div>
                <p class="text-sm font-semibold text-gray-500 mb-1">Hadir Tepat Waktu</p>
                <h3 class="text-4xl font-bold text-gray-800"><?= $s['total_hadir'] ?? 0 ?><span class="text-lg text-gray-400 font-normal ml-1">Orang</span></h3>
                <p class="text-xs text-green-600 mt-2">dari <?= $s['total_pegawai'] ?? 0 ?> total pegawai</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-yellow-400 relative overflow-hidden group">
                <div class="absolute right-[-10px] top-[10px] text-yellow-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-clock text-8xl"></i></div>
                <p class="text-sm font-semibold text-gray-500 mb-1">Terlambat Masuk</p>
                <h3 class="text-4xl font-bold text-gray-800"><?= $s['total_telat'] ?? 0 ?><span class="text-lg text-gray-400 font-normal ml-1">Orang</span></h3>
                <p class="text-xs text-yellow-600 mt-2">Perlu perhatian</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-red-400 relative overflow-hidden group">
                <div class="absolute right-[-10px] top-[10px] text-red-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-user-xmark text-8xl"></i></div>
                <p class="text-sm font-semibold text-gray-500 mb-1">Belum Absen</p>
                <h3 class="text-4xl font-bold text-gray-800"><?= $s['total_belum_absen'] ?? 0 ?><span class="text-lg text-gray-400 font-normal ml-1">Orang</span></h3>
                <p class="text-xs text-red-600 mt-2">Sampai saat ini</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart Tren 7 Hari -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Tren Kehadiran 7 Hari Terakhir</h3>
                <div style="height: 220px; position: relative;">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>

            <!-- Panel Kanan: Belum Absen + Cuti Pending -->
            <div class="space-y-6">
                <!-- Daftar Belum Absen -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Belum Absen Hari Ini
                    </h3>
                    <?php if (empty($belum_absen)): ?>
                    <p class="text-xs text-gray-400 text-center py-3"><i class="fa-solid fa-check-circle text-green-400 mr-1"></i>Semua pegawai sudah absen!</p>
                    <?php else: ?>
                    <ul class="space-y-2 max-h-40 overflow-y-auto">
                        <?php foreach ($belum_absen as $b): ?>
                        <li class="flex items-center gap-2 text-xs text-gray-700">
                            <div class="w-7 h-7 rounded-full bg-red-100 text-red-600 font-bold flex items-center justify-center text-[10px] flex-shrink-0">
                                <?= mb_substr($b['nama_lengkap'], 0, 2) ?>
                            </div>
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($b['nama_lengkap']) ?></p>
                                <p class="text-gray-400"><?= $b['jabatan'] ?></p>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- Cuti Pending -->
                <?php $pending_cuti = array_filter($cuti_pending, fn($c) => $c['status'] === 'pending'); ?>
                <?php if (!empty($pending_cuti)): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
                    <h3 class="text-sm font-bold text-yellow-800 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-bell text-yellow-500 animate-pulse"></i>
                        Cuti Menunggu ACC
                    </h3>
                    <ul class="space-y-2">
                        <?php foreach (array_slice($pending_cuti, 0, 4) as $c): ?>
                        <li class="text-xs text-yellow-800">
                            <a href="<?= BASE_URL ?>/admincabang/cuti" class="flex items-center gap-2 hover:text-yellow-900">
                                <div class="w-6 h-6 rounded-full bg-yellow-300 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                    <?= mb_substr($c['nama_lengkap'], 0, 2) ?>
                                </div>
                                <div>
                                    <p class="font-semibold"><?= htmlspecialchars($c['nama_lengkap']) ?></p>
                                    <p class="text-yellow-600"><?= $c['jenis'] ?> · <?= date('d M', strtotime($c['tanggal_mulai'])) ?></p>
                                </div>
                                <i class="fa-solid fa-arrow-right ml-auto text-yellow-400"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= BASE_URL ?>/admincabang/cuti" class="mt-3 block text-center text-xs font-bold text-yellow-700 hover:underline">Lihat & Proses Semua →</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const labels = <?= $tren_labels ?> || [];
    const dataHadir = <?= $tren_hadir ?> || [];
    const dataTelat = <?= $tren_telat ?> || [];

    const formattedLabels = labels.map(t => {
        const d = new Date(t);
        return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
    });

    new Chart(document.getElementById('chartTren'), {
        type: 'bar',
        data: {
            labels: formattedLabels.length ? formattedLabels : ['Tidak ada data'],
            datasets: [
                {
                    label: 'Hadir',
                    data: dataHadir,
                    backgroundColor: 'rgba(34,197,94,0.7)',
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Terlambat',
                    data: dataTelat,
                    backgroundColor: 'rgba(245,158,11,0.7)',
                    borderRadius: 6,
                    borderSkipped: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
