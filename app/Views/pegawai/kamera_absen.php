<div class="bg-black min-h-screen relative flex flex-col justify-between overflow-hidden max-w-md mx-auto">
    
    <!-- Bagian Atas: Indikator Jaringan & Kembali -->
    <div class="absolute top-0 left-0 right-0 z-20 flex justify-between items-center p-6 bg-gradient-to-b from-black/80 to-transparent">
        <a href="<?= BASE_URL ?>/pegawai" class="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        
        <!-- Indikator Jaringan Real-Time -->
        <div id="network-status" class="px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 flex items-center gap-2 transition-all">
            <div id="ping-dot" class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse"></div>
            <span id="ping-text" class="text-white text-xs font-semibold tracking-wider">Sinyal Kuat</span>
        </div>
    </div>

    <!-- Frame Kamera -->
    <div class="flex-1 relative flex items-center justify-center bg-gray-900 overflow-hidden">
        <video id="kamera-stream" autoplay playsinline class="absolute w-full h-full object-cover"></video>
        <!-- Label mode absen di tengah atas frame -->
        <div class="absolute top-20 left-0 right-0 flex justify-center z-10">
            <span class="px-5 py-2 rounded-full text-xs font-bold uppercase tracking-widest backdrop-blur-md border <?= ($mode ?? 'masuk') === 'pulang' ? 'bg-orange-500/70 border-orange-300 text-white' : 'bg-primary/70 border-blue-300 text-white' ?>">
                <i class="fa-solid <?= ($mode ?? 'masuk') === 'pulang' ? 'fa-right-from-bracket' : 'fa-right-to-bracket' ?> mr-2"></i>
                Absen <?= ucfirst($mode ?? 'masuk') ?>
            </span>
        </div>
        <!-- Reticle Kamera -->
        <div class="absolute w-64 h-64 border-2 border-white/30 border-dashed rounded-2xl flex items-center justify-center z-10">
        </div>
        <canvas id="canvas-kamera" class="hidden"></canvas>
    </div>

    <!-- Bagian Bawah: Informasi Lokasi & Tombol Shutter -->
    <div class="absolute bottom-0 left-0 right-0 z-20 bg-gradient-to-t from-black via-black/90 to-transparent pt-20 pb-8 px-6 flex flex-col items-center">
        
        <!-- Tombol Switch Kamera (Dipindah ke atas shutter) -->
        <button onclick="gantiKamera()" class="absolute -top-6 right-6 w-12 h-12 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/20 hover:bg-white/30 active:scale-95 transition-all shadow-lg z-30">
            <i class="fa-solid fa-camera-rotate text-xl"></i>
        </button>

        <!-- Koordinat / Alamat Teks -->
        <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl mb-6 text-center border border-white/10 w-full shadow-inner">
            <p class="text-gray-300 text-[10px] uppercase font-bold tracking-widest mb-1"><i class="fa-solid fa-location-dot mr-1"></i> Lokasi Anda</p>
            <p id="teks-kordinat" class="text-white text-xs font-mono leading-relaxed">Mencari Satelit GPS...</p>
        </div>

        <!-- Tombol Shutter Besar -->
        <button id="btn-shutter" onclick="ambilAbsen()" class="w-20 h-20 rounded-full bg-primary border-4 border-white shadow-[0_0_20px_rgba(30,64,175,0.6)] flex items-center justify-center active:scale-90 transition-transform disabled:opacity-50 disabled:active:scale-100">
            <i class="fa-solid fa-fingerprint text-3xl text-white"></i>
        </button>
        <p class="text-white/60 text-xs mt-4 font-medium">Ketuk untuk Ambil Absen</p>
    </div>
</div>

<!-- Logika Aplikasi (Kamera, GPS, Jaringan, Ajax) -->
<script>
    let streamVideo;
    let currentFacingMode = "user"; // Status kamera depan/belakang
    let latitude = null;
    let longitude = null;
    let isOffline = false;
    let alamatDidapat = false; // Status apakah alamat sudah diterjemahkan

    // 1. Inisialisasi Kamera Depan/Belakang
    async function mulaiKamera(facingMode = "user") {
        // Deteksi apakah API diblokir browser (Biasa terjadi jika pakai HTTP bukan HTTPS/localhost)
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                title: 'Akses Diblokir Browser',
                html: 'Kamera tidak bisa diakses pada domain <b>http://</b> (selain localhost) karena aturan keamanan Google Chrome.<br><br><b>SOLUSI TESTING:</b><br>Gunakan URL: <br><a href="http://localhost/ren_absensi/public" class="text-blue-500 font-bold">http://localhost/ren_absensi/public</a><br>Atau aktifkan fitur SSL (HTTPS) pada Laragon Anda.',
                icon: 'error'
            });
            return;
        }

        // Matikan kamera yang sedang aktif sebelum mengganti
        if (streamVideo) {
            streamVideo.getTracks().forEach(track => track.stop());
        }

        try {
            const constraints = { video: { facingMode: { exact: facingMode } } }; 
            streamVideo = await navigator.mediaDevices.getUserMedia(constraints);
            document.getElementById('kamera-stream').srcObject = streamVideo;
        } catch (err) {
            try {
                const constraintsFallback = { video: { facingMode: facingMode } };
                streamVideo = await navigator.mediaDevices.getUserMedia(constraintsFallback);
                document.getElementById('kamera-stream').srcObject = streamVideo;
            } catch(e) {
                Swal.fire('Izin Ditolak', 'Kamera tidak dapat diakses. Pastikan Anda mengizinkan (Allow) akses kamera pada *popup* browser di pojok kiri atas URL.', 'error');
            }
        }
    }

    // Fungsi Ganti Kamera (Rotate)
    function gantiKamera() {
        currentFacingMode = currentFacingMode === "user" ? "environment" : "user";
        mulaiKamera(currentFacingMode);
    }

    // 2. Inisialisasi GPS & Terjemahan Alamat (Reverse Geocoding)
    function mulaiGPS() {
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition((position) => {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;
                
                // Jika alamat belum didapat, lakukan fetch API ke OpenStreetMap
                if (!alamatDidapat && isOffline === false) {
                    document.getElementById('teks-kordinat').innerText = "Menerjemahkan lokasi Anda...";
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data && data.display_name) {
                                // Ambil 3 segmen pertama dari alamat agar tidak kepanjangan
                                const alamatPendek = data.display_name.split(',').slice(0, 3).join(', ');
                                document.getElementById('teks-kordinat').innerText = alamatPendek;
                                document.getElementById('teks-kordinat').classList.remove('font-mono');
                                document.getElementById('teks-kordinat').classList.add('font-semibold');
                                alamatDidapat = true;
                            }
                        })
                        .catch(() => {
                            // Fallback jika API gagal
                            document.getElementById('teks-kordinat').innerText = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                            alamatDidapat = true;
                        });
                } else if(!alamatDidapat) {
                    document.getElementById('teks-kordinat').innerText = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                }
            }, (error) => {
                document.getElementById('teks-kordinat').innerText = "Gagal mendapatkan lokasi.";
                Swal.fire('Izin GPS Ditolak', 'Sistem gagal mendapatkan lokasi. Pastikan Anda memberikan izin (Allow Location) di browser.', 'error');
            }, { enableHighAccuracy: true });
        } else {
            document.getElementById('teks-kordinat').innerText = "GPS diblokir oleh Browser (Butuh HTTPS/Localhost).";
        }
    }

    // 3. Simulasi Pengecekan Jaringan (Ping Sederhana)
    function cekJaringan() {
        setInterval(() => {
            const isOnline = navigator.onLine;
            const dot = document.getElementById('ping-dot');
            const teks = document.getElementById('ping-text');
            
            if (isOnline) {
                isOffline = false;
                dot.className = "w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse";
                teks.innerText = "Sinyal Baik";
            } else {
                isOffline = true;
                dot.className = "w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse";
                teks.innerText = "Sinyal Buruk";
            }
        }, 3000);
    }

    // 4. Proses Eksekusi Absen
    function ambilAbsen() {
        if (!latitude || !longitude) {
            Swal.fire('Tunggu', 'Lokasi GPS belum terkunci. Tunggu beberapa detik.', 'warning');
            return;
        }

        // Tangkap frame gambar dari video
        const video = document.getElementById('kamera-stream');
        const canvas = document.getElementById('canvas-kamera');
        const context = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert ke base64 (Format JPEG kualitas menengah agar ringan)
        const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);

        // UI Loading State pada tombol
        const btn = document.getElementById('btn-shutter');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-3xl text-white"></i>';
        btn.disabled = true;

        // Kirim Data via AJAX (sertakan mode: masuk/pulang)
        fetch('<?= BASE_URL ?>/pegawai/simpan_absen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'latitude': latitude,
                'longitude': longitude,
                'foto': fotoBase64,
                'mode': '<?= $mode ?? 'masuk' ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = '<i class="fa-solid fa-fingerprint text-3xl text-white"></i>';
            btn.disabled = false;

            if (data.status === 'success') {
                // Notifikasi custom jika sinyal offline tapi data terkirim (PWA scenario, dll)
                if(isOffline) {
                    Swal.fire('Peringatan Sinyal', 'Absen terkirim, namun jaringan lambat. Cek riwayat untuk memastikan.', 'warning').then(() => {
                        window.location.href = '<?= BASE_URL ?>/pegawai';
                    });
                } else {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        window.location.href = '<?= BASE_URL ?>/pegawai';
                    });
                }
            } else if (data.code === 'BELUM_JAM_PULANG') {
                // Popup khusus: belum jam pulang
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Waktunya Pulang',
                    text: data.message,
                    confirmButtonText: 'Oke, Saya Mengerti',
                    confirmButtonColor: '#f59e0b',
                    showClass: { popup: 'animate__animated animate__shakeX' }
                });
            } else {
                Swal.fire('Gagal Absen', data.message, 'error');
            }
        })
        .catch(err => {
            btn.innerHTML = '<i class="fa-solid fa-fingerprint text-3xl text-white"></i>';
            btn.disabled = false;
            Swal.fire('Error Server', 'Terjadi gangguan koneksi ke server pusat.', 'error');
        });
    }

    // Jalankan semua saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        mulaiKamera();
        mulaiGPS();
        cekJaringan();
    });
</script>
