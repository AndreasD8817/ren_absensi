<!-- Container utama mode Mobile -->
<div class="max-w-md mx-auto bg-gray-50 min-h-screen relative pb-24 shadow-lg border-x border-gray-200">
    
    <!-- Header / Profil -->
    <div class="bg-primary rounded-b-[40px] px-6 pt-10 pb-16 text-white shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama_lengkap'])[0]) ?>!</h1>
                <p class="text-blue-200 text-sm mt-1"><?= htmlspecialchars($_SESSION['user']['jabatan']) ?></p>
            </div>
            <div class="w-14 h-14 rounded-full bg-white/20 p-1 flex items-center justify-center backdrop-blur-sm border border-white/30">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama_lengkap']) ?>&background=random" alt="Profil" class="w-full h-full rounded-full object-cover">
            </div>
        </div>
        
        <!-- Jam Digital -->
        <div class="text-center mt-4">
            <p class="text-sm font-medium text-blue-200 uppercase tracking-wider mb-1" id="current-date"></p>
            <h2 class="text-5xl font-bold tracking-tight drop-shadow-md" id="current-time">--:--<span class="text-xl ml-1 font-medium">--</span></h2>
        </div>
    </div>

    <!-- Konten Utama yang overlap ke header -->
    <div class="px-6 -mt-10">

        <?php
        // --- Tentukan tampilan status absen hari ini ---
        $absen = $absensi_hari_ini ?? null;
        $sudah_masuk = !empty($absen['jam_masuk']);
        $sudah_pulang = !empty($absen['jam_pulang']);
        ?>

        <?php if (!empty($pengumuman)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 shadow-sm flex items-center gap-3 overflow-hidden">
            <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-white flex-shrink-0 animate-pulse">
                <i class="fa-solid fa-bullhorn text-sm"></i>
            </div>
            <div class="flex-1 overflow-hidden relative" style="height: 20px;">
                <div class="marquee-text whitespace-nowrap text-xs font-bold text-yellow-800">
                    <?php 
                    $text_pengumuman = [];
                    foreach ($pengumuman as $p) {
                        $text_pengumuman[] = $p['judul'] . ': ' . $p['isi'];
                    }
                    echo implode(' &nbsp; | &nbsp; ', $text_pengumuman);
                    ?>
                </div>
            </div>
        </div>
        <style>
            .marquee-text { display: inline-block; padding-left: 100%; animation: marquee 15s linear infinite; }
            @keyframes marquee { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }
        </style>
        <?php endif; ?>

        <!-- Kartu Status Absen Hari Ini -->
        <div class="bg-white rounded-2xl shadow-lg p-5 mb-6 border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Status Absen Hari Ini</h3>
            <div class="flex gap-3">
                <!-- Status Masuk -->
                <div class="flex-1 rounded-xl p-3 text-center <?= $sudah_masuk ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' ?>">
                    <i class="fa-solid fa-right-to-bracket text-xl <?= $sudah_masuk ? 'text-green-500' : 'text-gray-300' ?>"></i>
                    <p class="text-xs text-gray-500 mt-1">Masuk</p>
                    <p class="font-bold text-sm <?= $sudah_masuk ? 'text-green-700' : 'text-gray-400' ?>">
                        <?= $sudah_masuk ? date('H:i', strtotime($absen['jam_masuk'])) : '--:--' ?>
                    </p>
                    <?php if($sudah_masuk && $absen['status'] === 'telat'): ?>
                        <span class="text-[10px] text-red-500 font-semibold">Terlambat <?= $absen['menit_terlambat'] ?>m</span>
                    <?php elseif($sudah_masuk): ?>
                        <span class="text-[10px] text-green-600 font-semibold">Tepat Waktu</span>
                    <?php endif; ?>
                </div>
                <!-- Status Pulang -->
                <div class="flex-1 rounded-xl p-3 text-center <?= $sudah_pulang ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200' ?>">
                    <i class="fa-solid fa-right-from-bracket text-xl <?= $sudah_pulang ? 'text-blue-500' : 'text-gray-300' ?>"></i>
                    <p class="text-xs text-gray-500 mt-1">Pulang</p>
                    <p class="font-bold text-sm <?= $sudah_pulang ? 'text-blue-700' : 'text-gray-400' ?>">
                        <?= $sudah_pulang ? date('H:i', strtotime($absen['jam_pulang'])) : '--:--' ?>
                    </p>
                    <?php if($sudah_pulang): ?>
                        <span class="text-[10px] text-blue-600 font-semibold">Tercatat</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tombol Absen -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Tombol Absen Masuk: disable jika sudah masuk -->
            <?php if (!$sudah_masuk): ?>
                <a href="<?= BASE_URL ?>/pegawai/kamera/masuk" class="bg-gradient-to-br from-secondary to-primary text-white rounded-2xl p-4 shadow-lg flex flex-col items-center gap-3 transition-transform active:scale-95">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fa-solid fa-right-to-bracket text-xl"></i>
                    </div>
                    <span class="font-semibold text-center text-sm">Absen Masuk</span>
                </a>
            <?php else: ?>
                <div class="bg-gray-200 text-gray-400 rounded-2xl p-4 flex flex-col items-center gap-3 cursor-not-allowed">
                    <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-check text-xl text-gray-500"></i>
                    </div>
                    <span class="font-semibold text-center text-sm">Sudah Masuk</span>
                </div>
            <?php endif; ?>

            <!-- Tombol Absen Pulang: aktif hanya jika sudah masuk dan belum pulang -->
            <?php if ($sudah_masuk && !$sudah_pulang): ?>
                <a href="<?= BASE_URL ?>/pegawai/kamera/pulang" class="bg-white text-primary border-2 border-primary rounded-2xl p-4 shadow-sm flex flex-col items-center gap-3 transition-transform active:scale-95">
                    <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket text-xl text-primary"></i>
                    </div>
                    <span class="font-semibold text-center text-sm">Absen Pulang</span>
                </a>
            <?php elseif ($sudah_pulang): ?>
                <div class="bg-gray-200 text-gray-400 rounded-2xl p-4 flex flex-col items-center gap-3 cursor-not-allowed">
                    <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-check text-xl text-gray-500"></i>
                    </div>
                    <span class="font-semibold text-center text-sm">Sudah Pulang</span>
                </div>
            <?php else: ?>
                <div class="bg-gray-100 text-gray-400 rounded-2xl p-4 flex flex-col items-center gap-3 cursor-not-allowed opacity-60">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket text-xl text-gray-400"></i>
                    </div>
                    <span class="font-semibold text-center text-sm">Absen Pulang</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Riwayat 5 Hari Terakhir -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-700 mb-3 text-sm uppercase tracking-wider">Riwayat Kehadiran</h3>
            <?php if (empty($riwayat)): ?>
                <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                    <i class="fa-regular fa-calendar-xmark text-3xl text-gray-300 mb-2"></i>
                    <p class="text-sm text-gray-400">Belum ada riwayat absensi.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($riwayat as $r):
                        $tgl_fmt = date('d M Y', strtotime($r['tanggal']));
                        $hari_fmt = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'][date('D', strtotime($r['tanggal']))];
                        $status_class = $r['status'] === 'hadir' ? 'bg-green-100 text-green-700' : ($r['status'] === 'telat' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                        $status_text = ucfirst($r['status']);
                    ?>
                    <div class="bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-regular fa-calendar text-primary"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm"><?= $hari_fmt ?>, <?= $tgl_fmt ?></p>
                            <p class="text-xs text-gray-500">
                                Masuk: <b><?= $r['jam_masuk'] ? date('H:i', strtotime($r['jam_masuk'])) : '-' ?></b>
                                &nbsp;|&nbsp; Pulang: <b><?= $r['jam_pulang'] ? date('H:i', strtotime($r['jam_pulang'])) : '-' ?></b>
                            </p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold flex-shrink-0 <?= $status_class ?>"><?= $status_text ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== RIWAYAT INSENTIF ===== -->
    <?php if (!empty($riwayat_insentif)): ?>
    <div class="px-5 pb-4">
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-gift text-white text-xs"></i>
                </div>
                <h3 class="font-bold text-sm text-emerald-800">Riwayat Insentif Diterima</h3>
            </div>
            <div class="space-y-2">
                <?php
                $nama_bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                foreach ($riwayat_insentif as $ins): ?>
                <div class="bg-white rounded-xl px-4 py-3 flex justify-between items-center shadow-sm">
                    <div>
                        <p class="text-xs font-semibold text-gray-700"><?= $ins['keterangan'] ?: 'Insentif Bulanan' ?></p>
                        <p class="text-[10px] text-gray-400 mt-0.5"><?= $nama_bln[$ins['bulan']] ?> <?= $ins['tahun'] ?> · Dicairkan <?= date('d M', strtotime($ins['tanggal_input'])) ?></p>
                    </div>
                    <span class="text-sm font-black text-emerald-600">+Rp <?= number_format($ins['nilai_didapat'],0,',','.') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bottom Navigation Bar -->
    <div class="fixed bottom-0 max-w-md w-full bg-white border-t border-gray-200 rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.05)] px-4 py-3 flex justify-between items-center z-50">
        <a href="<?= BASE_URL ?>/pegawai" class="flex flex-col items-center gap-1 text-primary transition-colors">
            <div class="p-2 bg-blue-50 rounded-xl"><i class="fa-solid fa-house text-lg"></i></div>
            <span class="text-[10px] font-semibold">Home</span>
        </a>
        <a href="<?= BASE_URL ?>/pegawai/penggajian" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition-colors">
            <div class="p-2"><i class="fa-solid fa-file-invoice-dollar text-lg"></i></div>
            <span class="text-[10px] font-medium">Slip Gaji</span>
        </a>
        <a href="<?= BASE_URL ?>/pegawai/lembur" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition-colors">
            <div class="p-2"><i class="fa-solid fa-user-clock text-lg"></i></div>
            <span class="text-[10px] font-medium">Lembur</span>
        </a>
        <a href="<?= BASE_URL ?>/pegawai/cuti" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition-colors">
            <div class="p-2"><i class="fa-solid fa-calendar-alt text-lg"></i></div>
            <span class="text-[10px] font-medium">Cuti</span>
        </a>
        <a href="#" onclick="konfirmasiLogout(event)" class="flex flex-col items-center gap-1 text-red-400 hover:text-red-600 transition-colors">
            <div class="p-2"><i class="fa-solid fa-power-off text-lg"></i></div>
            <span class="text-[10px] font-medium">Logout</span>
        </a>
    </div>

</div>

<!-- Script Interaksi -->
<script>
    function konfirmasiLogout(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Keluar Sistem?',
            text: "Anda akan keluar dari akun Anda.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= BASE_URL ?>/auth/logout';
            }
        })
    }

    // Fungsi update jam & tanggal secara real-time
    function updateTime() {
        const now = new Date();
        const namaHari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        
        let hours = now.getHours();
        let minutes = now.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;

        document.getElementById('current-time').innerHTML = hours + ':' + minutes + '<span class="text-xl ml-1 font-medium">' + ampm + '</span>';
        document.getElementById('current-date').innerText = namaHari[now.getDay()] + ', ' + now.getDate() + ' ' + namaBulan[now.getMonth()] + ' ' + now.getFullYear();
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>
