<?php
$current_uri = $_SERVER['REQUEST_URI'];
function isActive($path) {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'bg-blue-700/40 text-white border border-blue-500/30' : 'text-blue-200 hover:bg-blue-800/30 hover:text-white';
}
?>
<aside class="w-64 bg-gradient-to-b from-[#1e3a5f] to-[#162d4a] text-white flex-shrink-0 flex flex-col shadow-2xl">
    <div class="px-6 py-8 border-b border-blue-800/30">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-fingerprint text-2xl text-blue-300"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight">PT REN</h1>
                <p class="text-blue-400 text-[10px] font-medium uppercase tracking-widest">Pusat Administrasi</p>
            </div>
        </div>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <a href="<?= BASE_URL ?>/superadmin" class="flex items-center px-4 py-3 <?= isActive('/superadmin') && !isActive('data_') && !isActive('penggajian') ? 'bg-blue-700/40 text-white border border-blue-500/30' : 'text-blue-200 hover:bg-blue-800/30 hover:text-white' ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-gauge w-5"></i><span class="ml-3 text-sm">Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/data_pegawai" class="flex items-center px-4 py-3 <?= isActive('data_pegawai') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-users w-5"></i><span class="ml-3 text-sm">Kelola Pegawai</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/data_cabang" class="flex items-center px-4 py-3 <?= isActive('data_cabang') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-building w-5"></i><span class="ml-3 text-sm">Kelola Cabang</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/insentif" class="flex items-center px-4 py-3 <?= isActive('insentif') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-gift w-5"></i><span class="ml-3 text-sm">Insentif Cabang</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/libur_nasional" class="flex items-center px-4 py-3 <?= isActive('libur_nasional') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-calendar-day w-5"></i><span class="ml-3 text-sm">Kalender Libur</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/absensi" class="flex items-center px-4 py-3 <?= isActive('absensi') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-calendar-check w-5"></i><span class="ml-3 text-sm">Data Absensi</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/penggajian" class="flex items-center px-4 py-3 <?= isActive('penggajian') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-money-check-dollar w-5"></i><span class="ml-3 text-sm">Penggajian</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/cuti" class="flex items-center px-4 py-3 <?= isActive('cuti') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-file-circle-check w-5"></i><span class="ml-3 text-sm">Kelola Cuti & Izin</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/pengumuman" class="flex items-center px-4 py-3 <?= isActive('pengumuman') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-bullhorn w-5"></i><span class="ml-3 text-sm">Pengumuman</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/kas_denda" class="flex items-center px-4 py-3 <?= isActive('kas_denda') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-wallet w-5"></i><span class="ml-3 text-sm">Kas Denda Cabang</span>
        </a>
        <div class="pt-4 mt-4 border-t border-blue-800/30">
            <a href="#" onclick="konfirmasiLogout(event)" class="flex items-center px-4 py-3 text-red-300 hover:bg-red-900/50 hover:text-red-100 rounded-xl font-medium transition-all">
                <i class="fa-solid fa-power-off w-5"></i><span class="ml-3 text-sm">Logout</span>
            </a>
        </div>
    </nav>
    <div class="px-6 py-4 border-t border-blue-800/30">
        <div class="flex items-center gap-3">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama_lengkap']) ?>&background=random" class="w-8 h-8 rounded-full" alt="">
            <div>
                <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></p>
                <p class="text-blue-400 text-[10px]">Superadmin</p>
            </div>
        </div>
    </div>
</aside>

<script>
function konfirmasiLogout(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Keluar Sistem?', text: 'Anda akan keluar dari akun Superadmin.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Logout!', cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) window.location.href = '<?= BASE_URL ?>/auth/logout'; });
}
</script>
