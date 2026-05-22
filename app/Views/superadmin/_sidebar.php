<?php
$current_uri = $_SERVER['REQUEST_URI'];
function isActive($path) {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'bg-[#A3195A]/40 text-white border border-[#A3195A]/30' : 'text-blue-100 hover:bg-white/10 hover:text-white';
}
?>
<script>
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
    }
</script>

<style>
    /* Modern smooth scrollbar for sidebar nav */
    .sidebar-nav-container::-webkit-scrollbar {
        width: 6px;
    }
    .sidebar-nav-container::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-nav-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
    }
    .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    .sidebar-nav-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
    }

    /* Transition for layout elements */
    #sidebar {
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Collapsed state styles */
    .sidebar-collapsed #sidebar {
        width: 5rem !important; /* 80px */
    }
    .sidebar-collapsed #sidebar .sidebar-text,
    .sidebar-collapsed #sidebar .sidebar-profile-text,
    .sidebar-collapsed #sidebar .sidebar-header-text {
        display: none !important;
    }
    .sidebar-collapsed #sidebar .sidebar-header-container {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
        flex-direction: column;
        gap: 0.5rem;
    }
    .sidebar-collapsed #sidebar .sidebar-logo-container {
        margin: 0 auto;
        gap: 0;
    }
    .sidebar-collapsed #sidebar nav {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .sidebar-collapsed #sidebar nav a {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }
    .sidebar-collapsed #sidebar nav a i {
        margin-left: 0;
        margin-right: 0;
        font-size: 1.2rem;
    }
    .sidebar-collapsed #sidebar .sidebar-profile-container {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }
</style>

<aside id="sidebar" class="w-64 bg-gradient-to-b from-blue-900 via-blue-950 to-[#A3195A]/80 text-white flex-shrink-0 flex flex-col shadow-2xl transition-all duration-300">
    <div class="h-24 px-6 border-b border-white/10 flex items-center justify-between sidebar-header-container transition-all duration-300">
        <div class="flex items-center gap-3 sidebar-logo-container">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center p-1 shadow-lg flex-shrink-0">
                <img src="<?= BASE_URL ?>/img/logo.png" alt="Logo PT REN" class="w-full h-full object-contain">
            </div>
            <div class="sidebar-header-text transition-all duration-300 min-w-0">
                <h1 class="text-lg font-black tracking-tight text-white leading-none">PT REN</h1>
                <p class="text-blue-300 text-[10px] font-semibold uppercase tracking-wider mt-1 whitespace-nowrap">Pusat Administrasi</p>
            </div>
        </div>
        <button onclick="toggleSidebar()" class="text-blue-200 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-all flex items-center justify-center flex-shrink-0" id="sidebar-toggle-btn" title="Toggle Sidebar">
            <i class="fa-solid fa-chevron-left" id="sidebar-toggle-icon"></i>
        </button>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto sidebar-nav-container">
        
        <div class="text-[10px] font-bold text-blue-300/70 uppercase tracking-widest mt-1 mb-2 px-4 sidebar-text">Utama</div>
        <a href="<?= BASE_URL ?>/superadmin" class="flex items-center px-4 py-3 <?= isActive('/superadmin') && !isActive('data_') && !isActive('penggajian') ? 'bg-[#A3195A]/40 text-white border border-[#A3195A]/30' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-gauge w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/pengumuman" class="flex items-center px-4 py-3 <?= isActive('pengumuman') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-bullhorn w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Pengumuman</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/audit_log" class="flex items-center px-4 py-3 <?= isActive('audit_log') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-shield-halved w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Log Aktivitas</span>
        </a>

        <div class="text-[10px] font-bold text-blue-300/70 uppercase tracking-widest mt-6 mb-2 px-4 sidebar-text">Master Data</div>
        <a href="<?= BASE_URL ?>/superadmin/data_pegawai" class="flex items-center px-4 py-3 <?= isActive('data_pegawai') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-users w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Kelola Pegawai</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/data_cabang" class="flex items-center px-4 py-3 <?= isActive('data_cabang') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-building w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Kelola Cabang</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/libur_nasional" class="flex items-center px-4 py-3 <?= isActive('libur_nasional') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-calendar-day w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Kalender Libur</span>
        </a>

        <div class="text-[10px] font-bold text-blue-300/70 uppercase tracking-widest mt-6 mb-2 px-4 sidebar-text">Operasional</div>
        <a href="<?= BASE_URL ?>/superadmin/absensi" class="flex items-center px-4 py-3 <?= isActive('absensi') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-calendar-check w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Data Absensi</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/cuti" class="flex items-center px-4 py-3 <?= isActive('cuti') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-file-circle-check w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Kelola Cuti & Izin</span>
        </a>

        <div class="text-[10px] font-bold text-blue-300/70 uppercase tracking-widest mt-6 mb-2 px-4 sidebar-text">Keuangan</div>
        <a href="<?= BASE_URL ?>/superadmin/penggajian" class="flex items-center px-4 py-3 <?= isActive('penggajian') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-money-check-dollar w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Penggajian</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/bonus_thr" class="flex items-center px-4 py-3 <?= isActive('bonus_thr') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-hand-holding-dollar w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Kelola Bonus & THR</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/insentif" class="flex items-center px-4 py-3 <?= isActive('insentif') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-gift w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Insentif Cabang</span>
        </a>
        <a href="<?= BASE_URL ?>/superadmin/kas_denda" class="flex items-center px-4 py-3 <?= isActive('kas_denda') ?> rounded-xl font-medium transition-all">
            <i class="fa-solid fa-wallet w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Kas Denda Cabang</span>
        </a>

        <div class="pt-4 mt-6 border-t border-white/10">
            <a href="#" onclick="konfirmasiLogout(event)" class="flex items-center px-4 py-3 text-red-300 hover:bg-red-900/50 hover:text-red-100 rounded-xl font-medium transition-all">
                <i class="fa-solid fa-power-off w-5 text-center"></i><span class="ml-3 text-sm sidebar-text">Logout</span>
            </a>
        </div>
    </nav>
    <div class="px-6 py-4 border-t border-white/10 flex items-center gap-3 sidebar-profile-container">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama_lengkap']) ?>&background=random" class="w-8 h-8 rounded-full flex-shrink-0" alt="">
        <div class="sidebar-profile-text transition-all duration-300 min-w-0">
            <p class="text-white text-xs font-semibold truncate"><?= esc($_SESSION['user']['nama_lengkap']) ?></p>
            <p class="text-blue-300 text-[10px]">Superadmin</p>
        </div>
    </div>
</aside>

<script>
function toggleSidebar() {
    const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', isCollapsed);
    updateSidebarIcon();
}

function updateSidebarIcon() {
    const icon = document.getElementById('sidebar-toggle-icon');
    if (!icon) return;
    if (document.documentElement.classList.contains('sidebar-collapsed')) {
        icon.className = 'fa-solid fa-chevron-right';
    } else {
        icon.className = 'fa-solid fa-chevron-left';
    }
}

// Set initial icon state on load
updateSidebarIcon();
document.addEventListener('DOMContentLoaded', updateSidebarIcon);

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
