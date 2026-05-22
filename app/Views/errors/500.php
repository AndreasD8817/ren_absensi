<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan Sistem - PT REN</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/img/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Abstract Backgrounds -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute top-40 -left-40 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-40 left-20 w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Error Card -->
    <div class="relative z-10 max-w-lg w-full bg-white/70 backdrop-blur-xl border border-white/50 shadow-2xl rounded-[2rem] p-8 md:p-12 text-center">
        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-red-200">
            <i class="fa-solid fa-triangle-exclamation text-4xl text-red-500"></i>
        </div>
        
        <h1 class="text-3xl font-black text-gray-800 mb-2 tracking-tight">Oops! Terjadi Kesalahan</h1>
        <p class="text-gray-500 mb-8 leading-relaxed">
            Sistem kami sedang mengalami gangguan atau menemukan instruksi yang tidak terduga. Tim teknis kami telah mencatat masalah ini secara otomatis.
        </p>

        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 mb-8 text-left">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-info text-blue-500 mt-1"></i>
                <div>
                    <h3 class="text-sm font-bold text-gray-700">Apa yang harus saya lakukan?</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-snug">Silakan kembali ke halaman utama, memuat ulang halaman, atau hubungi Administrator jika masalah ini terus berlanjut.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="javascript:history.back()" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl shadow-sm hover:bg-gray-50 active:scale-95 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <a href="/" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 active:scale-95 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-house"></i> Halaman Utama
            </a>
        </div>
    </div>
</body>
</html>
