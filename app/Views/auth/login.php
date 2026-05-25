<div class="min-h-screen bg-gray-100 flex flex-col justify-center py-10 px-4 sm:px-0 bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
    <!-- Overlay gelap dengan perpaduan biru dan #A3195A -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/90 via-blue-900/80 to-[#A3195A]/80 backdrop-blur-sm"></div>

    <!-- Container Khusus Login -->
    <div class="relative w-full max-w-md mx-auto bg-gray-50 rounded-[30px] shadow-2xl flex flex-col sm:border border-gray-200 overflow-hidden">
        
        <!-- Header melengkung seperti Dashboard dengan perpaduan warna -->
        <div class="bg-gradient-to-br from-primary via-blue-800 to-[#A3195A] pt-12 pb-20 px-6 text-center text-white rounded-b-[40px] shadow-lg relative shrink-0">
            <!-- Dekorasi background melengkung abstrak -->
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white/10 blur-xl"></div>
            <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-24 h-24 rounded-full bg-[#A3195A]/40 blur-lg"></div>
            
            <div class="relative z-10 inline-flex items-center justify-center w-28 h-28 rounded-full bg-white shadow-xl mb-4 border-4 border-white/50 p-1 overflow-hidden">
                <img src="<?= BASE_URL ?>/img/logo.png" alt="Logo PT REN" class="w-full h-full object-contain">
            </div>
            <h2 class="relative z-10 text-3xl font-extrabold tracking-tight drop-shadow-md">PT REN</h2>
            <p class="relative z-10 mt-2 text-sm text-blue-100 font-medium tracking-wide">Sistem Absensi Terpadu</p>
        </div>

        <!-- Area Form yang overlap ke header -->
        <div class="flex-1 px-6 -mt-12 pb-10">
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 mb-6 relative z-20">
                
                <?php if(isset($_SESSION['flash_error'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-exclamation text-red-500 mr-3"></i>
                            <p class="text-xs text-red-700 font-medium">
                                <?= $_SESSION['flash_error']; ?>
                            </p>
                        </div>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['flash_success'])): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-md">
                        <div class="flex items-center">
                            <i class="fa-solid fa-check-circle text-green-500 mr-3"></i>
                            <p class="text-xs text-green-700 font-medium">
                                <?= $_SESSION['flash_success']; ?>
                            </p>
                        </div>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if(isset($block_android) && $block_android): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center shadow-inner">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-mobile-screen-button text-red-500 text-3xl"></i>
                        </div>
                        <h3 class="text-red-700 font-bold text-lg mb-2">Akses Ditolak</h3>
                        <p class="text-xs text-red-600 font-medium mb-4 leading-relaxed">
                            Berdasarkan kebijakan keamanan terbaru, pengguna perangkat Android <strong>DIWAJIBKAN</strong> menggunakan Aplikasi Resmi (APK) PT REN untuk melakukan absensi.
                        </p>
                        <div class="bg-white p-3 rounded-lg border border-red-100">
                            <p class="text-[11px] text-gray-500 font-medium">Silakan hubungi Administrator / HRD untuk mendapatkan *link* unduhan aplikasi resmi kami.</p>
                        </div>
                    </div>
                <?php else: ?>
                <form class="space-y-5" action="<?= BASE_URL ?>/auth/proses_login" method="POST">
                    <?= csrf_field() ?>
                    <div>
                        <label for="nip" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">NIP Pegawai</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-id-card text-gray-400"></i>
                            </div>
                            <input id="nip" name="nip" type="text" inputmode="numeric" required class="block w-full pl-11 px-4 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#A3195A]/50 focus:border-[#A3195A] focus:bg-white text-sm transition-all" placeholder="Contoh: 123456">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" required class="block w-full pl-11 pr-12 px-4 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#A3195A]/50 focus:border-[#A3195A] focus:bg-white text-sm transition-all" placeholder="••••••••">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword()">
                                <i id="eye-icon" class="fa-regular fa-eye text-gray-400 hover:text-[#A3195A] transition-colors text-lg"></i>
                            </div>
                        </div>
                        <div class="flex justify-end mt-3">
                            <a href="#" class="text-xs font-bold text-[#A3195A] hover:text-primary transition-colors">Lupa Sandi?</a>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 rounded-xl shadow-lg shadow-[#A3195A]/20 text-sm font-bold text-white bg-gradient-to-r from-primary to-[#A3195A] hover:from-blue-800 hover:to-[#8a154c] focus:outline-none transition-all active:scale-95">
                            <i class="fa-solid fa-right-to-bracket"></i> MASUK SISTEM
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            
            <div class="mt-6 text-center text-xs text-gray-400 font-medium">
                <p>&copy; 2026 PT REN. V1.0.0</p>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
            eyeIcon.classList.add('text-[#A3195A]');
            eyeIcon.classList.remove('text-primary');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
            eyeIcon.classList.remove('text-[#A3195A]');
            eyeIcon.classList.add('text-primary');
        }
    }
</script>