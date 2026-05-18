<!-- Dashboard Superadmin - Data Nyata -->
<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <!-- Konten Utama Kanan -->
    <main class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Header Atas -->
        <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 shadow-sm z-10">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Ringkasan Hari Ini</h2>
                <p class="text-sm text-gray-500"><?= date('l, d F Y') ?></p>
            </div>
            <div class="flex items-center gap-4">
                <?php $n_pending = count($cuti_pending); ?>
                <?php if ($n_pending > 0): ?>
                <a href="<?= BASE_URL ?>/superadmin/cuti?status=pending" class="relative flex items-center gap-2 px-4 py-2 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-xl text-sm font-semibold hover:bg-yellow-100 transition-colors">
                    <i class="fa-solid fa-bell animate-pulse"></i>
                    <?= $n_pending ?> Cuti Menunggu Persetujuan
                </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Area Konten Scrollable -->
        <div class="flex-1 overflow-y-auto p-8 space-y-8">
            
            <?php $s = $statistik; ?>
            <!-- Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-green-500 relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[10px] text-green-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-check-circle text-8xl"></i></div>
                    <p class="text-sm font-semibold text-gray-500 mb-1">Hadir Tepat Waktu</p>
                    <h3 class="text-4xl font-bold text-gray-800"><?= $s['total_hadir'] ?? 0 ?><span class="text-lg text-gray-400 font-normal ml-1">/ <?= $s['total_pegawai'] ?? 0 ?></span></h3>
                    <p class="text-xs text-green-600 mt-2 font-medium"><i class="fa-solid fa-user-check mr-1"></i>Pegawai hadir hari ini</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-yellow-400 relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[10px] text-yellow-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-clock text-8xl"></i></div>
                    <p class="text-sm font-semibold text-gray-500 mb-1">Terlambat Masuk</p>
                    <h3 class="text-4xl font-bold text-gray-800"><?= $s['total_telat'] ?? 0 ?><span class="text-lg text-gray-400 font-normal ml-1">Orang</span></h3>
                    <p class="text-xs text-yellow-600 mt-2 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>Perlu perhatian</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-red-400 relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[10px] text-red-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-user-xmark text-8xl"></i></div>
                    <p class="text-sm font-semibold text-gray-500 mb-1">Belum Absen</p>
                    <h3 class="text-4xl font-bold text-gray-800"><?= $s['total_belum_absen'] ?? 0 ?><span class="text-lg text-gray-400 font-normal ml-1">Orang</span></h3>
                    <p class="text-xs text-red-600 mt-2 font-medium"><i class="fa-solid fa-hourglass mr-1"></i>Belum tercatat</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 p-6 transition-all border-l-4 border-l-blue-400 relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[10px] text-blue-50 opacity-40 group-hover:scale-110 transition-transform"><i class="fa-solid fa-file-circle-check text-8xl"></i></div>
                    <p class="text-sm font-semibold text-gray-500 mb-1">Cuti Menunggu ACC</p>
                    <h3 class="text-4xl font-bold text-gray-800"><?= $n_pending ?><span class="text-lg text-gray-400 font-normal ml-1">Pengajuan</span></h3>
                    <p class="text-xs text-blue-600 mt-2 font-medium">
                        <a href="<?= BASE_URL ?>/superadmin/cuti?status=pending" class="hover:underline"><i class="fa-solid fa-arrow-right mr-1"></i>Lihat semua</a>
                    </p>
                </div>
            </div>

            <!-- Chart & Cuti Pending -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart Tren 7 Hari -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Tren Kehadiran 7 Hari Terakhir</h3>
                    <div style="height: 250px; position: relative;">
                        <canvas id="chartTren"></canvas>
                    </div>
                </div>

                <!-- Cuti Pending (notif inline) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-800">Cuti Menunggu Pusat</h3>
                        <a href="<?= BASE_URL ?>/superadmin/cuti" class="text-xs text-primary font-semibold hover:underline">Lihat Semua</a>
                    </div>
                    <?php if (empty($cuti_pending)): ?>
                    <div class="flex-1 flex flex-col items-center justify-center text-center text-gray-400">
                        <i class="fa-solid fa-check-circle text-4xl text-green-300 mb-2"></i>
                        <p class="text-sm font-medium">Tidak ada pengajuan yang menunggu</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3 overflow-y-auto flex-1 max-h-56 pr-1">
                        <?php foreach ($cuti_pending as $c): ?>
                        <a href="<?= BASE_URL ?>/superadmin/cuti?status=pending#cuti-<?= $c['id_cuti'] ?>" class="block bg-yellow-50 border border-yellow-100 rounded-xl p-3 hover:bg-yellow-100 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-yellow-300 text-yellow-900 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    <?= mb_substr($c['nama_lengkap'], 0, 2) ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 text-xs truncate"><?= htmlspecialchars($c['nama_lengkap']) ?></p>
                                    <p class="text-[10px] text-gray-500"><?= $c['nama_cabang'] ?> · <?= $c['jenis'] ?></p>
                                    <p class="text-[10px] text-yellow-700 font-medium">
                                        <?= date('d M', strtotime($c['tanggal_mulai'])) ?> – <?= date('d M Y', strtotime($c['tanggal_selesai'])) ?>
                                    </p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-yellow-400 text-xs flex-shrink-0"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const labels = <?= $tren_labels ?> || [];
    const dataHadir = <?= $tren_hadir ?> || [];
    const dataTelat = <?= $tren_telat ?> || [];

    // Format label tanggal jadi lebih pendek
    const formattedLabels = labels.map(t => {
        const d = new Date(t);
        return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
    });

    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: formattedLabels.length ? formattedLabels : ['Tidak ada data'],
            datasets: [
                {
                    label: 'Hadir',
                    data: dataHadir,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.08)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#22c55e',
                    pointRadius: 4
                },
                {
                    label: 'Terlambat',
                    data: dataTelat,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.06)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 }, boxWidth: 12 } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
</script>
