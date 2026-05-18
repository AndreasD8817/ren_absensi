<?php
$current_uri = $_SERVER['REQUEST_URI'];
function isActiveAdmin($path) {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'bg-orange-600/40 text-white border border-orange-500/30' : 'text-orange-200 hover:bg-orange-800/30 hover:text-white';
}
?>
<aside class="w-64 bg-gradient-to-b from-[#7c2d12] to-[#431407] text-white flex-shrink-0 flex flex-col shadow-2xl">
    <div class="px-6 py-8 border-b border-orange-800/30">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-fingerprint text-2xl text-orange-300"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight">PT REN</h1>
                <p class="text-orange-400 text-[10px] font-medium uppercase tracking-widest">Admin Cabang</p>
            </div>
        </div>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <a href="<?= BASE_URL ?>/admincabang" class="flex items-center px-4 py-3 <?= isActiveAdmin('/admincabang') && !isActiveAdmin('cuti') && !isActiveAdmin('pengaturan') ? 'bg-orange-600/40 text-white border border-orange-500/30' : 'text-orange-200 hover:bg-orange-800/30 hover:text-white' ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-gauge w-5"></i><span class="ml-3 text-sm">Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/admincabang/cuti" class="flex items-center px-4 py-3 <?= isActiveAdmin('cuti') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-calendar-alt w-5"></i><span class="ml-3 text-sm">Persetujuan Cuti</span>
        </a>
        <a href="<?= BASE_URL ?>/admincabang/lembur" class="flex items-center px-4 py-3 <?= isActiveAdmin('lembur') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-user-clock w-5"></i><span class="ml-3 text-sm">Persetujuan Lembur</span>
        </a>
        <a href="<?= BASE_URL ?>/admincabang/libur_lokal" class="flex items-center px-4 py-3 <?= isActiveAdmin('libur_lokal') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-map-location-dot w-5"></i><span class="ml-3 text-sm">Libur Lokal</span>
        </a>
        <a href="<?= BASE_URL ?>/admincabang/pengaturan" class="flex items-center px-4 py-3 <?= isActiveAdmin('pengaturan') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-gear w-5"></i><span class="ml-3 text-sm">Pengaturan Cabang</span>
        </a>
        <a href="<?= BASE_URL ?>/admincabang/kas_denda" class="flex items-center px-4 py-3 <?= isActiveAdmin('kas_denda') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-wallet w-5"></i><span class="ml-3 text-sm">Kas Denda Cabang</span>
        </a>
        <div class="pt-4 mt-4 border-t border-orange-800/30">
            <a href="#" onclick="konfirmasiLogoutAdmin(event)" class="flex items-center px-4 py-3 text-red-300 hover:bg-red-900/50 hover:text-red-100 rounded-xl font-medium transition-all">
                <i class="fa-solid fa-power-off w-5"></i><span class="ml-3 text-sm">Logout</span>
            </a>
        </div>
    </nav>
    <div class="px-6 py-4 border-t border-orange-800/30">
        <div class="flex items-center gap-3">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama_lengkap']) ?>&background=random" class="w-8 h-8 rounded-full" alt="">
            <div>
                <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></p>
                <p class="text-orange-400 text-[10px]">Admin Cabang</p>
            </div>
        </div>
    </div>
</aside>

<script>
function konfirmasiLogoutAdmin(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Keluar Sistem?', text: 'Anda akan keluar dari akun Admin Cabang.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Logout!', cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) window.location.href = '<?= BASE_URL ?>/auth/logout'; });
}
</script>
