<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-800 tracking-tight">Pusat Pencadangan (Backup)</h2>
            <p class="text-gray-500 mt-2 text-sm max-w-2xl">Download seluruh data sistem, mulai dari struktur database, rekam jejak, absensi, hingga seluruh berkas foto secara utuh dalam satu file ZIP.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl mb-8">
            <!-- Mode Pemeliharaan -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm mb-1"><i class="fa-solid fa-lock text-red-500 mr-2"></i> Mode Pemeliharaan</h3>
                    <p class="text-xs text-gray-500">Kunci login untuk Pegawai & Admin Cabang</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="toggle-maintenance" class="sr-only peer" onchange="toggleMaintenance(this.checked)" <?= (file_exists(APP_PATH . '/Config/maintenance.json') && json_decode(file_get_contents(APP_PATH . '/Config/maintenance.json'), true)['is_maintenance']) ? 'checked' : '' ?>>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                </label>
            </div>

            <!-- Storage Capacity Bar -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm lg:col-span-2 flex items-center gap-6">
                <div class="flex-1">
                    <div class="flex justify-between items-end mb-2">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm mb-1"><i class="fa-solid fa-hard-drive text-blue-500 mr-2"></i> Kapasitas Folder Foto</h3>
                            <p class="text-xs text-gray-500" id="storage-text">Klik update untuk menghitung penggunaan (Maks 4GB)</p>
                        </div>
                        <span class="text-sm font-bold text-gray-700" id="storage-percentage">--%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div id="storage-bar" class="bg-gray-300 h-3 rounded-full transition-all duration-1000" style="width: 0%"></div>
                    </div>
                </div>
                <button onclick="hitungKapasitas()" id="btn-storage" class="px-4 py-2.5 bg-blue-50 text-blue-600 font-bold text-xs rounded-xl hover:bg-blue-100 transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-arrows-rotate"></i> Hitung Kapasitas
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl">
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
                    <p class="text-xs text-center text-gray-400 mt-4 mb-6"><i class="fa-solid fa-circle-info mr-1"></i> Proses ini mungkin memakan waktu agak lama.</p>
                    
                    <hr class="border-gray-100 mb-6">
                    
                    <h3 class="text-sm font-bold text-red-600 mb-3"><i class="fa-solid fa-trash-can mr-1"></i> Hapus Foto Bulan Tertentu</h3>
                    <div class="flex gap-2">
                        <input type="month" id="bulan_hapus" class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all text-gray-700">
                        <button onclick="hapusFotoBulan()" id="btn-hapus-foto" class="px-5 py-2.5 bg-red-100 text-red-600 font-bold rounded-xl hover:bg-red-200 transition-colors text-sm shadow-sm whitespace-nowrap">
                            Hapus
                        </button>
                    </div>
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

    async function toggleMaintenance(isChecked) {
        const resp = await fetch('<?= BASE_URL ?>/superadmin/toggle_maintenance', {
            method: 'POST',
            body: new URLSearchParams({ status: isChecked ? '1' : '0', csrf_token: '<?= csrf_token() ?>' })
        });
        const data = await resp.json();
        if(data.status === 'success') {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            Toast.fire({ icon: isChecked ? 'warning' : 'success', title: isChecked ? 'Mode Pemeliharaan AKTIF' : 'Mode Pemeliharaan MATI' });
        }
    }

    async function hitungKapasitas() {
        const btn = document.getElementById('btn-storage');
        const text = document.getElementById('storage-text');
        const bar = document.getElementById('storage-bar');
        const pct = document.getElementById('storage-percentage');
        
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/superadmin/hitung_kapasitas');
        const data = await resp.json();
        
        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Hitung Ulang';
        btn.disabled = false;

        if(data.status === 'success') {
            text.innerHTML = `Terpakai: <b>${data.terpakai_mb} MB</b> dari Kuota ${data.kuota_mb} MB (Maks 4GB)`;
            pct.innerText = `${data.persentase}%`;
            bar.style.width = `${data.persentase}%`;
            
            if(data.persentase < 50) { bar.className = 'bg-green-500 h-3 rounded-full transition-all duration-1000'; }
            else if(data.persentase < 85) { bar.className = 'bg-yellow-400 h-3 rounded-full transition-all duration-1000'; }
            else { bar.className = 'bg-red-500 h-3 rounded-full transition-all duration-1000'; }
        }
    }

    function hapusFotoBulan() {
        const bulan = document.getElementById('bulan_hapus').value;
        if(!bulan) { Swal.fire('Pilih Bulan', 'Silakan pilih bulan yang ingin dihapus fotonya.', 'warning'); return; }

        Swal.fire({
            title: 'Hapus Permanen?',
            html: `Seluruh foto di bulan <b>${bulan}</b> akan dihapus selamanya dari sistem untuk membebaskan ruang penyimpanan.<br><br><b>PENTING:</b> Pastikan Anda sudah mem-backup-nya terlebih dahulu!`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Permanen!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btn-hapus-foto');
                const textAwal = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                btn.disabled = true;

                const resp = await fetch('<?= BASE_URL ?>/superadmin/hapus_foto_berkala', {
                    method: 'POST', body: new URLSearchParams({ bulan: bulan, csrf_token: '<?= csrf_token() ?>' })
                });
                const data = await resp.json();
                
                btn.innerHTML = textAwal;
                btn.disabled = false;

                if (data.status === 'success') {
                    Swal.fire('Berhasil Dihapus!', data.message, 'success');
                    document.getElementById('bulan_hapus').value = '';
                    hitungKapasitas(); // Otomatis update bar setelah hapus
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            }
        });
    }
</script>
