<!-- Log Aktivitas Superadmin -->
<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <!-- Konten Utama -->
    <main class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Header Atas -->
        <header class="h-24 bg-gradient-to-r from-[#1E40AF] via-blue-900 to-[#A3195A] text-white flex items-center justify-between px-8 shadow-md z-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-24 h-24 rounded-full bg-white/10 blur-lg"></div>
            
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center p-1.5 shadow-md">
                    <img src="<?= BASE_URL ?>/img/logo.png" alt="Logo PT REN" class="w-full h-full object-contain">
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-white leading-tight">PT Renggali Anugerah Utama</h2>
                    <p class="text-xs text-blue-200 font-semibold tracking-wide uppercase">Pusat Administrasi (PT REN)</p>
                </div>
            </div>
            
            <div class="text-right relative z-10 hidden md:block">
                <p class="text-xs font-semibold text-blue-200 uppercase tracking-wider"><?= date('l') ?></p>
                <p class="text-sm font-bold text-white"><?= date('d F Y') ?></p>
            </div>
        </header>

        <!-- Area Konten Scrollable -->
        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            
            <!-- Page Title -->
            <div class="flex justify-between items-end mb-4">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Log Aktivitas Sistem</h2>
                    <p class="text-sm text-gray-500 mt-1">Pantau seluruh aktivitas user di aplikasi secara real-time</p>
                </div>
                <!-- Filter Search bisa diletakkan di sini nantinya -->
            </div>

            <!-- Tabel Log -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">Aksi</th>
                                <th class="px-6 py-4">Entitas</th>
                                <th class="px-6 py-4">Keterangan</th>
                                <th class="px-6 py-4">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                        <p>Belum ada data log aktivitas.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                                        <?= esc(date('d/m/Y H:i:s', strtotime($log['created_at']))) ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        <?= esc($log['nama_user']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                            <?= $log['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : ($log['role'] === 'admin_cabang' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>">
                                            <?= esc($log['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                            $color = 'text-gray-600 bg-gray-50';
                                            if ($log['aksi'] === 'CREATE' || str_contains($log['aksi'], 'MASUK')) $color = 'text-emerald-700 bg-emerald-50';
                                            if ($log['aksi'] === 'UPDATE' || $log['aksi'] === 'LOGIN') $color = 'text-blue-700 bg-blue-50';
                                            if ($log['aksi'] === 'DELETE' || str_contains($log['aksi'], 'PULANG') || $log['aksi'] === 'LOGOUT') $color = 'text-rose-700 bg-rose-50';
                                        ?>
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold <?= $color ?>">
                                            <?= esc($log['aksi']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                        <?= esc($log['entitas']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 truncate max-w-xs" title="<?= esc($log['keterangan']) ?>">
                                        <?= esc($log['keterangan']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-400">
                                        <?= esc($log['ip_address']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </main>
</div>
