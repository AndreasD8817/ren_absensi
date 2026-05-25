<!-- Wrapper untuk Background Desktop ala Login -->
<div class="min-h-screen bg-gray-100 flex flex-col sm:justify-center sm:py-12 bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
    <!-- Overlay gelap -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/90 via-blue-900/80 to-[#A3195A]/80 backdrop-blur-sm"></div>

    <!-- Container utama mode Mobile -->
    <div class="w-full max-w-md mx-auto bg-gray-50 h-[100dvh] overflow-hidden sm:h-[85vh] sm:rounded-[30px] relative pb-20 shadow-2xl border-x sm:border border-gray-200 z-10 flex flex-col">
    
    <!-- Header / Profil -->
    <div class="bg-gradient-to-br from-primary via-blue-800 to-[#A3195A] rounded-b-[40px] text-white shadow-xl relative overflow-hidden shrink-0">
        <!-- Dekorasi background melengkung abstrak -->
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white/10 blur-xl"></div>
        <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-24 h-24 rounded-full bg-[#A3195A]/40 blur-lg"></div>

        <?php if (!empty($pengumuman)): ?>
        <!-- Marquee Ticker -->
        <div class="bg-black/20 backdrop-blur-sm py-2.5 px-6 text-xs flex items-center gap-2 border-b border-white/10 relative z-20">
            <i class="fa-solid fa-bullhorn text-yellow-300 animate-pulse flex-shrink-0"></i>
            <div class="flex-1 overflow-hidden relative" style="height: 16px;">
                <div class="marquee-text whitespace-nowrap text-white/90 font-medium">
                    <?php 
                    $text_pengumuman = [];
                    foreach ($pengumuman as $p) {
                        $text_pengumuman[] = esc($p['judul']) . ': ' . esc($p['isi']);
                    }
                    echo implode(' &nbsp; &nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp; &nbsp; ', $text_pengumuman);
                    ?>
                </div>
            </div>
        </div>
        <style>
            .marquee-text { display: inline-block; padding-left: 100%; animation: marquee-header 25s linear infinite; }
            @keyframes marquee-header { 0% { transform: translate3d(0, 0, 0); } 100% { transform: translate3d(-100%, 0, 0); } }
        </style>
        <?php endif; ?>

        <!-- Inner Content (Profil & Jam) -->
        <div class="px-6 pt-5 pb-12 relative z-10">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Halo, <?= esc(explode(' ', $_SESSION['user']['nama_lengkap'])[0]) ?>!</h1>
                    <p class="text-blue-100 text-sm mt-1"><?= esc($_SESSION['user']['jabatan']) ?></p>
                </div>
                <div class="w-14 h-14 rounded-full bg-white/20 p-1 flex items-center justify-center backdrop-blur-sm border border-white/30">
                    <img src="<?= BASE_URL ?>/img/logo.png" alt="Logo REN" class="w-full h-full rounded-full object-contain bg-white">
                </div>
            </div>
            
            <!-- Jam Digital -->
            <div class="text-center mt-2">
                <p class="text-xs font-medium text-blue-100 uppercase tracking-wider mb-1" id="current-date"></p>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight drop-shadow-md font-mono" id="current-time">--:--:--</h2>
            </div>
        </div>
    </div>

    <!-- Konten Utama yang overlap ke header -->
    <div class="px-5 -mt-8 flex-1 flex flex-col relative z-20 min-h-0">

        <?php
        // --- Tentukan tampilan status absen hari ini ---
        $absen = $absensi_hari_ini ?? null;
        $sudah_masuk = !empty($absen['jam_masuk']);
        $sudah_pulang = !empty($absen['jam_pulang']);
        ?>

        <!-- Kartu Status Absen Hari Ini -->
        <div class="bg-white rounded-2xl shadow-lg p-3 mb-4 border border-gray-100 shrink-0">
            <h3 class="font-bold text-gray-800 mb-2 text-xs uppercase tracking-wider">Status Hari Ini</h3>
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
        <div class="grid grid-cols-2 gap-3 mb-4 shrink-0">
            <!-- Tombol Absen Masuk: disable jika sudah masuk -->
            <?php if (!$sudah_masuk): ?>
                <a href="<?= BASE_URL ?>/pegawai/kamera/masuk" class="bg-gradient-to-r from-primary to-[#A3195A] text-white rounded-2xl p-4 shadow-lg shadow-[#A3195A]/20 flex flex-col items-center gap-3 transition-transform hover:from-blue-800 hover:to-[#8a154c] active:scale-95">
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
                <a href="<?= BASE_URL ?>/pegawai/kamera/pulang" class="bg-white text-[#A3195A] border-2 border-[#A3195A] rounded-2xl p-4 shadow-sm flex flex-col items-center gap-3 transition-transform hover:bg-gray-50 active:scale-95">
                    <div class="w-12 h-12 bg-[#A3195A]/10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket text-xl text-[#A3195A]"></i>
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

        <!-- Riwayat Kehadiran (Kalender) -->
        <div class="flex-1 min-h-0 bg-white rounded-2xl p-4 mb-4 shadow-sm border border-gray-100 flex flex-col relative">
            <h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider flex items-center justify-between">
                <span>Riwayat Kehadiran</span>
                <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-md font-bold">26-25</span>
            </h3>
            
            <div class="grid grid-cols-7 gap-1 text-center mb-1 shrink-0">
                <div class="text-[10px] font-bold text-red-400">Min</div>
                <div class="text-[10px] font-bold text-gray-500">Sen</div>
                <div class="text-[10px] font-bold text-gray-500">Sel</div>
                <div class="text-[10px] font-bold text-gray-500">Rab</div>
                <div class="text-[10px] font-bold text-gray-500">Kam</div>
                <div class="text-[10px] font-bold text-gray-500">Jum</div>
                <div class="text-[10px] font-bold text-blue-400">Sab</div>
            </div>
            
            <div id="calendar-grid" class="grid grid-cols-7 gap-1 flex-1 content-start overflow-y-auto pb-2 pr-1 custom-scrollbar">
                <!-- JS will populate the calendar here -->
            </div>
            <style>
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 4px; }
            </style>
        </div>
    </div>

    <!-- ===== RIWAYAT INSENTIF ===== -->
    <?php if (!empty($riwayat_insentif)): ?>
    <div class="px-5 pb-4 shrink-0 relative z-20">
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl overflow-hidden transition-all duration-300 shadow-sm" id="insentif-container">
            <button onclick="toggleInsentif()" class="w-full px-4 py-3 flex justify-between items-center bg-white/40 hover:bg-white/60 transition-colors active:scale-[0.99]">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-gift text-white text-[10px]"></i>
                    </div>
                    <h3 class="font-bold text-xs text-emerald-800">Riwayat Insentif (Dapat Ditarik)</h3>
                </div>
                <i id="insentif-icon" class="fa-solid fa-chevron-down text-emerald-600 text-sm transition-transform duration-300"></i>
            </button>
            <div id="insentif-content" class="h-0 overflow-y-auto transition-all duration-300 px-3 custom-scrollbar">
                <div class="space-y-2 pb-3 pt-1">
                    <?php
                    $nama_bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    foreach ($riwayat_insentif as $ins): ?>
                    <div class="bg-white rounded-xl px-3 py-2 flex justify-between items-center shadow-sm">
                        <div>
                            <p class="text-xs font-semibold text-gray-700"><?= $ins['keterangan'] ?: 'Insentif Bulanan' ?></p>
                            <p class="text-[10px] text-gray-400 mt-0.5"><?= $nama_bln[$ins['bulan']] ?> <?= $ins['tahun'] ?></p>
                        </div>
                        <span class="text-xs font-black text-emerald-600">+Rp <?= number_format($ins['nilai_didapat'],0,',','.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bottom Navigation Bar -->
    <div class="fixed sm:absolute bottom-0 max-w-md w-full bg-white border-t border-gray-200 rounded-t-3xl sm:rounded-b-[30px] sm:rounded-t-none shadow-[0_-10px_40px_rgba(0,0,0,0.05)] px-4 py-3 flex justify-between items-center z-50">
        <a href="<?= BASE_URL ?>/pegawai" class="flex flex-col items-center gap-1 text-[#A3195A] transition-colors">
            <div class="p-2 bg-[#A3195A]/10 rounded-xl"><i class="fa-solid fa-house text-lg"></i></div>
            <span class="text-[10px] font-semibold">Home</span>
        </a>
        <a href="<?= BASE_URL ?>/pegawai/penggajian" class="flex flex-col items-center gap-1 text-gray-400 hover:text-[#A3195A] transition-colors">
            <div class="p-2"><i class="fa-solid fa-file-invoice-dollar text-lg"></i></div>
            <span class="text-[10px] font-medium">Slip Gaji</span>
        </a>
        <a href="<?= BASE_URL ?>/pegawai/lembur" class="flex flex-col items-center gap-1 text-gray-400 hover:text-[#A3195A] transition-colors">
            <div class="p-2"><i class="fa-solid fa-user-clock text-lg"></i></div>
            <span class="text-[10px] font-medium">Lembur</span>
        </a>
        <a href="<?= BASE_URL ?>/pegawai/cuti" class="flex flex-col items-center gap-1 text-gray-400 hover:text-[#A3195A] transition-colors">
            <div class="p-2"><i class="fa-solid fa-calendar-alt text-lg"></i></div>
            <span class="text-[10px] font-medium">Cuti</span>
        </a>
        <a href="#" onclick="konfirmasiLogout(event)" class="flex flex-col items-center gap-1 text-red-400 hover:text-red-600 transition-colors">
            <div class="p-2"><i class="fa-solid fa-power-off text-lg"></i></div>
            <span class="text-[10px] font-medium">Logout</span>
        </a>
    </div>

    </div> <!-- End Container Utama -->
</div> <!-- End Wrapper Background -->

<!-- Modal Kalender Detail -->
<div id="modal-cal" class="fixed inset-0 z-[60] hidden items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="tutupCal(event)">
    <div class="bg-white w-full max-w-md sm:rounded-3xl rounded-t-3xl p-6 transform transition-transform duration-300 translate-y-full shadow-2xl" id="modal-cal-content" onclick="event.stopPropagation()">
        <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-lg text-gray-800" id="m-cal-tgl">--</h3>
                <p class="text-sm font-bold mt-1" id="m-cal-status">--</p>
            </div>
            <button onclick="tutupCal()" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-2">
            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Jam Masuk</p>
                <p class="font-bold text-gray-800 text-lg font-mono" id="m-cal-masuk">--:--</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Jam Pulang</p>
                <p class="font-bold text-gray-800 text-lg font-mono" id="m-cal-pulang">--:--</p>
            </div>
        </div>
        <p class="text-center text-xs font-bold mt-3 mb-6" id="m-cal-telat"></p>
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

    // --- Jam Server Sinkronisasi ---
    let s_h = <?= (int)($server_h ?? date('H')) ?>;
    let s_m = <?= (int)($server_m ?? date('i')) ?>;
    let s_s = <?= (int)($server_s ?? date('s')) ?>;

    const namaHari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const serverDateStr = new Date('<?= $server_date ?? date('Y-m-d') ?>');

    function updateTime() {
        s_s++;
        if(s_s >= 60) { s_s = 0; s_m++; }
        if(s_m >= 60) { s_m = 0; s_h++; }
        if(s_h >= 24) { s_h = 0; serverDateStr.setDate(serverDateStr.getDate() + 1); }
        
        let hh = s_h < 10 ? '0'+s_h : s_h;
        let mm = s_m < 10 ? '0'+s_m : s_m;
        let ss = s_s < 10 ? '0'+s_s : s_s;

        document.getElementById('current-time').innerHTML = hh + ':' + mm + ':' + ss;
        document.getElementById('current-date').innerText = namaHari[serverDateStr.getDay()] + ', ' + serverDateStr.getDate() + ' ' + namaBulan[serverDateStr.getMonth()] + ' ' + serverDateStr.getFullYear();
    }
    updateTime();
    setInterval(updateTime, 1000);

    // --- Riwayat Insentif Accordion ---
    function toggleInsentif() {
        const content = document.getElementById('insentif-content');
        const icon = document.getElementById('insentif-icon');
        if (content.classList.contains('h-0')) {
            content.classList.remove('h-0');
            content.classList.add('h-40');
            icon.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('h-0');
            content.classList.remove('h-40');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    // --- Kalender Grid Absensi ---
    const kalenderData = <?= json_encode($kalender_absen ?? []) ?>;
    const grid = document.getElementById('calendar-grid');

    function renderKalender() {
        if(!kalenderData || kalenderData.length === 0) return;
        
        // Cari index hari pertama
        const firstDate = new Date(kalenderData[0].tanggal);
        const firstDay = firstDate.getDay();
        
        // Kotak kosong awal bulan
        for(let i=0; i<firstDay; i++) {
            grid.innerHTML += `<div class="p-1"></div>`;
        }
        
        kalenderData.forEach(d => {
            const tgl = new Date(d.tanggal).getDate();
            let colorClass = 'bg-gray-50 text-red-500 border border-red-100'; // Default alfa
            let dotColor = 'bg-red-400';
            
            if(d.status === 'hadir') {
                colorClass = 'bg-green-50 text-green-700 border border-green-200';
                dotColor = 'bg-green-500';
            } else if(d.status === 'telat') {
                colorClass = 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                dotColor = 'bg-yellow-500';
            } else if(d.status === 'cuti') {
                colorClass = 'bg-teal-50 text-teal-700 border border-teal-200';
                dotColor = 'bg-teal-500';
            } else if(d.status === 'libur' || d.status === 'weekend') {
                colorClass = 'bg-gray-100 text-gray-400 border border-gray-200';
                dotColor = 'bg-gray-300';
            }
            
            const dStr = JSON.stringify(d).replace(/'/g, "&#39;");
            const html = `
                <div onclick='bukaCal(${dStr})' class="aspect-square flex flex-col items-center justify-center rounded-xl cursor-pointer hover:scale-105 active:scale-95 transition-transform ${colorClass} shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                    <span class="text-xs font-bold">${tgl}</span>
                    <div class="w-1.5 h-1.5 rounded-full mt-0.5 ${dotColor}"></div>
                </div>
            `;
            grid.innerHTML += html;
        });
    }
    renderKalender();

    // --- Modal Kalender ---
    const mCal = document.getElementById('modal-cal');
    const mCalContent = document.getElementById('modal-cal-content');

    function bukaCal(data) {
        const dDate = new Date(data.tanggal);
        document.getElementById('m-cal-tgl').innerText = dDate.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
        
        let statusText = '';
        let statusColor = '';
        if(data.status === 'hadir') { statusText = 'Tepat Waktu'; statusColor = 'text-green-600 bg-green-100 px-3 py-1 rounded-full inline-block'; }
        else if(data.status === 'telat') { statusText = 'Terlambat'; statusColor = 'text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full inline-block'; }
        else if(data.status === 'alfa') { statusText = 'Alfa / Tidak Hadir'; statusColor = 'text-red-600 bg-red-100 px-3 py-1 rounded-full inline-block'; }
        else if(data.status === 'cuti') { statusText = 'Cuti / Izin'; statusColor = 'text-teal-700 bg-teal-100 px-3 py-1 rounded-full inline-block'; }
        else { statusText = 'Libur / Akhir Pekan'; statusColor = 'text-gray-600 bg-gray-100 px-3 py-1 rounded-full inline-block'; }
        
        const elStatus = document.getElementById('m-cal-status');
        elStatus.innerText = statusText;
        elStatus.className = `text-xs font-bold mt-2 ${statusColor}`;
        
        document.getElementById('m-cal-masuk').innerText = data.jam_masuk ? data.jam_masuk.substring(0,5) : '--:--';
        document.getElementById('m-cal-pulang').innerText = data.jam_pulang ? data.jam_pulang.substring(0,5) : '--:--';
        
        const elTelat = document.getElementById('m-cal-telat');
        if(data.menit_terlambat > 0) {
            elTelat.innerText = `Terlambat ${data.menit_terlambat} menit`;
            elTelat.className = 'text-center text-xs font-bold mt-3 mb-4 text-red-500';
        } else {
            elTelat.innerText = '';
            elTelat.className = 'hidden';
        }
        
        mCal.classList.remove('hidden');
        mCal.classList.add('flex');
        setTimeout(() => {
            mCal.classList.remove('opacity-0');
            mCalContent.classList.remove('translate-y-full');
        }, 10);
    }

    function tutupCal(e) {
        if(e && e.target !== mCal && e.target.tagName !== 'BUTTON' && !e.target.closest('button')) return;
        mCal.classList.add('opacity-0');
        mCalContent.classList.add('translate-y-full');
        setTimeout(() => {
            mCal.classList.add('hidden');
            mCal.classList.remove('flex');
        }, 300);
    }
</script>
