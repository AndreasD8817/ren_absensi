<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">Pusat Pencadangan (Backup)</h2>
            <p class="text-gray-500 mt-2 text-sm max-w-2xl">Download seluruh data sistem, mulai dari struktur database, rekam jejak, absensi, hingga seluruh berkas foto secara utuh dalam satu file ZIP.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl">
            <!-- Backup Full -->
            <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xl shadow-blue-900/5 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-blue-50 to-blue-100 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-inner">
                        <i class="fa-solid fa-file-zipper"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Full Archive (.zip)</h3>
                    <p class="text-gray-500 text-sm mb-6 leading-relaxed">Berisi salinan utuh <b>Database MySQL</b> (format .sql) beserta seluruh foto absensi & bukti cuti dari folder <b>/public/uploads</b>.</p>
                    
                    <button onclick="downloadBackup()" id="btn-backup" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition-all hover:shadow-blue-600/50 hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Mulai Download Backup
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-4"><i class="fa-solid fa-circle-info mr-1"></i> Proses ini mungkin memakan waktu agak lama tergantung ukuran foto.</p>
                </div>
            </div>
            
            <!-- Informasi Penting -->
            <div class="bg-gradient-to-br from-[#A3195A]/5 to-[#A3195A]/10 rounded-3xl p-8 border border-[#A3195A]/20">
                <h3 class="text-[#A3195A] font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Panduan Keamanan
                </h3>
                <ul class="space-y-4 text-sm text-gray-700">
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-check text-green-600 mt-0.5"></i>
                        <span>Simpan file ZIP ini di perangkat keras (Hard-disk / Flashdisk) atau layanan Cloud terpisah seperti Google Drive.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-check text-green-600 mt-0.5"></i>
                        <span>Jika ingin memindahkan (*migrasi*) aplikasi ke server/hosting baru, cukup masukkan (*import*) file .sql-nya ke PHPMyAdmin, lalu timpa folder `uploads`.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-lock text-[#A3195A] mt-0.5"></i>
                        <span>File .sql berisi *password* tersandi dan rekam jejak krusial. **Dilarang membagikannya** kepada pihak yang tidak berwenang.</span>
                    </li>
                </ul>
            </div>
        </div>
    </main>
</div>

<script>
    function downloadBackup() {
        const btn = document.getElementById('btn-backup');
        
        Swal.fire({
            title: 'Mempersiapkan Backup...',
            html: 'Sistem sedang mengekstrak database dan mengompresi ribuan foto menjadi file ZIP.<br><b class="text-red-500">Mohon jangan tutup halaman ini.</b>',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Alihkan halaman secara langsung ke endpoint download
        window.location.href = '<?= BASE_URL ?>/superadmin/proses_download_backup';
        
        // Kembalikan swal setelah jeda singkat karena browser akan mengunduh
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Pengunduhan Dimulai',
                text: 'File ZIP akan segera terunduh ke perangkat Anda. Jika gagal, silakan coba lagi.',
                confirmButtonColor: '#2563eb'
            });
        }, 3000);
    }
</script>
