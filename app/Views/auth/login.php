<div class="min-h-screen bg-gray-100 flex flex-col sm:justify-center sm:py-12 bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
    <!-- Overlay gelap -->
    <div class="absolute inset-0 bg-blue-900/80 backdrop-blur-sm"></div>

    <!-- Container khusus seukuran Mobile -->
    <div class="relative w-full max-w-md mx-auto bg-gray-50 min-h-screen sm:min-h-[85vh] sm:rounded-[30px] shadow-2xl flex flex-col sm:border border-gray-200">
        
        <!-- Header melengkung seperti Dashboard -->
        <div class="bg-primary pt-12 pb-20 px-6 text-center text-white rounded-b-[40px] shadow-lg">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 backdrop-blur-md shadow-xl mb-4 border border-white/30">
                <i class="fa-solid fa-fingerprint text-4xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold tracking-tight">PT REN</h2>
            <p class="mt-2 text-sm text-blue-200">Sistem Absensi Terpadu</p>
        </div>

        <!-- Area Form yang overlap ke header -->
        <div class="flex-1 px-6 -mt-12 pb-10">
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 mb-6">
                
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

                <form class="space-y-5" action="<?= BASE_URL ?>/auth/proses_login" method="POST">
                    <div>
                        <label for="nip" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">NIP Pegawai</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-id-card text-gray-400"></i>
                            </div>
                            <input id="nip" name="nip" type="text" inputmode="numeric" required class="block w-full pl-11 px-4 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary focus:bg-white text-sm transition-all" placeholder="Contoh: 123456">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" required class="block w-full pl-11 pr-12 px-4 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary focus:bg-white text-sm transition-all" placeholder="••••••••">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword()">
                                <i id="eye-icon" class="fa-regular fa-eye text-gray-400 hover:text-primary transition-colors text-lg"></i>
                            </div>
                        </div>
                        <div class="flex justify-end mt-3">
                            <a href="#" class="text-xs font-bold text-secondary hover:text-primary transition-colors">Lupa Sandi?</a>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 rounded-xl shadow-lg shadow-blue-500/30 text-sm font-bold text-white bg-gradient-to-r from-primary to-secondary hover:from-blue-800 hover:to-blue-600 focus:outline-none transition-transform active:scale-95">
                            <i class="fa-solid fa-right-to-bracket"></i> MASUK SISTEM
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center text-xs text-gray-500 bg-white shadow-sm rounded-xl p-4 border border-gray-100">
                <p class="font-medium mb-2 uppercase tracking-wider text-[10px]">Panduan Testing</p>
                <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg mb-1">
                    <span>Superadmin:</span>
                    <span class="font-mono font-bold text-primary">admin</span>
                </div>
                <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg mb-1">
                    <span>Pegawai:</span>
                    <span class="font-mono font-bold text-primary">123456</span>
                </div>
                <div class="flex justify-between items-center bg-blue-50 p-2 rounded-lg">
                    <span>Sandi:</span>
                    <span class="font-mono font-bold text-blue-700">password123</span>
                </div>
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
            eyeIcon.classList.add('text-primary');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
            eyeIcon.classList.remove('text-primary');
        }
    }
</script>
