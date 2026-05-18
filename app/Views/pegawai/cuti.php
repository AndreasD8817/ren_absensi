<!-- Kontainer Mobile Khusus Pegawai -->
<div class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 shadow-2xl relative overflow-hidden font-sans">
    
    <!-- Latar Belakang Desain -->
    <div class="absolute top-0 left-0 right-0 h-48 bg-gradient-to-b from-primary to-blue-700 rounded-b-[40px] z-0"></div>
    <div class="absolute top-10 right-[-20px] w-32 h-32 bg-white opacity-10 rounded-full blur-2xl z-0"></div>

    <!-- Header -->
    <header class="relative z-10 px-6 pt-10 pb-4 flex items-center justify-between text-white">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Pengajuan</h2>
            <p class="text-blue-200 text-sm mt-0.5">Cuti, Sakit & Izin</p>
        </div>
        <a href="<?= BASE_URL ?>/pegawai" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 active:scale-90 transition-transform">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
    </header>

    <!-- Konten Scrollable -->
    <div class="relative z-10 px-5 mt-2 space-y-6">

        <!-- Form Pengajuan -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-primary"></i> Buat Pengajuan Baru
            </h3>
            <form id="form-cuti" onsubmit="submitCuti(event)" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Pengajuan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="relative">
                            <input type="radio" name="jenis" value="Cuti" class="peer sr-only" required>
                            <div class="p-2 text-center rounded-xl border border-gray-200 text-gray-500 text-xs font-semibold cursor-pointer transition-all peer-checked:bg-blue-50 peer-checked:text-primary peer-checked:border-primary">Cuti</div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="jenis" value="Sakit" class="peer sr-only">
                            <div class="p-2 text-center rounded-xl border border-gray-200 text-gray-500 text-xs font-semibold cursor-pointer transition-all peer-checked:bg-red-50 peer-checked:text-red-500 peer-checked:border-red-400">Sakit</div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="jenis" value="Izin" class="peer sr-only">
                            <div class="p-2 text-center rounded-xl border border-gray-200 text-gray-500 text-xs font-semibold cursor-pointer transition-all peer-checked:bg-orange-50 peer-checked:text-orange-500 peer-checked:border-orange-400">Izin</div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dari Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" required min="<?= date('Y-m-d') ?>" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sampai Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_selesai" required min="<?= date('Y-m-d') ?>" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alasan / Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="keterangan" rows="2" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700 placeholder-gray-400" placeholder="Jelaskan alasan secara singkat..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Bukti Foto / Surat Dokter <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <div class="relative">
                        <input type="file" name="bukti_foto" id="bukti" accept="image/*" class="sr-only" onchange="previewFileName()">
                        <label for="bukti" class="flex items-center justify-between px-3 py-2.5 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-sm text-gray-500 cursor-pointer hover:bg-gray-100 transition-colors">
                            <span id="file-name" class="truncate"><i class="fa-solid fa-cloud-arrow-up mr-2"></i>Pilih Foto...</span>
                            <div class="w-7 h-7 bg-white shadow-sm rounded-lg flex items-center justify-center text-primary"><i class="fa-solid fa-camera text-xs"></i></div>
                        </label>
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="w-full py-3.5 bg-primary text-white rounded-xl font-bold text-sm shadow-[0_8px_20px_-6px_rgba(30,64,175,0.5)] active:scale-95 transition-all mt-2">
                    Kirim Pengajuan
                </button>
            </form>
        </div>

        <!-- Riwayat Pengajuan -->
        <div>
            <h3 class="font-bold text-gray-800 mb-3 px-1">Riwayat Pengajuan Saya</h3>
            
            <?php if (empty($riwayat_cuti)): ?>
            <div class="bg-white rounded-2xl p-8 text-center border border-dashed border-gray-200 shadow-sm">
                <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm font-semibold text-gray-500">Belum ada riwayat pengajuan.</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($riwayat_cuti as $c): 
                    $tgl_mulai = date('d M Y', strtotime($c['tanggal_mulai']));
                    $tgl_selesai = date('d M Y', strtotime($c['tanggal_selesai']));
                    
                    $bg_icon = 'bg-blue-100 text-blue-600'; $icon = 'fa-calendar-alt';
                    if ($c['jenis'] == 'Sakit') { $bg_icon = 'bg-red-100 text-red-500'; $icon = 'fa-notes-medical'; }
                    elseif ($c['jenis'] == 'Izin') { $bg_icon = 'bg-orange-100 text-orange-500'; $icon = 'fa-person-walking-arrow-right'; }

                    $bg_status = 'bg-gray-100 text-gray-500'; $icon_status = 'fa-clock'; $text_status = 'Menunggu';
                    if ($c['status'] == 'approved') { $bg_status = 'bg-green-100 text-green-700'; $icon_status = 'fa-check-circle'; $text_status = 'Disetujui'; }
                    elseif ($c['status'] == 'rejected') { $bg_status = 'bg-red-100 text-red-700'; $icon_status = 'fa-times-circle'; $text_status = 'Ditolak'; }
                ?>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl <?= $bg_icon ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid <?= $icon ?> text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-gray-800 text-sm"><?= $c['jenis'] ?></h4>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold <?= $bg_status ?> flex items-center gap-1"><i class="fa-solid <?= $icon_status ?>"></i> <?= $text_status ?></span>
                        </div>
                        <p class="text-[11px] font-medium text-gray-500 mb-1">
                            <?= $tgl_mulai == $tgl_selesai ? $tgl_mulai : "$tgl_mulai - $tgl_selesai" ?>
                        </p>
                        <p class="text-xs text-gray-600 truncate"><?= htmlspecialchars($c['keterangan']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    function previewFileName() {
        const file = document.getElementById('bukti').files[0];
        const span = document.getElementById('file-name');
        if (file) {
            span.innerHTML = '<i class="fa-solid fa-image text-primary mr-2"></i>' + file.name;
            span.classList.add('text-primary', 'font-semibold');
        } else {
            span.innerHTML = '<i class="fa-solid fa-cloud-arrow-up mr-2"></i>Pilih Foto...';
            span.classList.remove('text-primary', 'font-semibold');
        }
    }

    async function submitCuti(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        const form = document.getElementById('form-cuti');
        
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengirim...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/pegawai/ajukan_cuti', {
            method: 'POST',
            body: new FormData(form)
        });
        const data = await resp.json();

        if (data.status === 'success') {
            Swal.fire({
                title: 'Berhasil!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#1e3a5f'
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.innerHTML = 'Kirim Pengajuan';
            btn.disabled = false;
        }
    }
</script>
