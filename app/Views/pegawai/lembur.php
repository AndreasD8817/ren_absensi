<!-- Kontainer Mobile Khusus Pegawai -->
<div class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24 shadow-2xl relative overflow-hidden font-sans">
    
    <!-- Latar Belakang Desain -->
    <div class="absolute top-0 left-0 right-0 h-48 bg-gradient-to-b from-primary to-blue-700 rounded-b-[40px] z-0"></div>
    <div class="absolute top-10 right-[-20px] w-32 h-32 bg-white opacity-10 rounded-full blur-2xl z-0"></div>

    <!-- Header -->
    <header class="relative z-10 px-6 pt-10 pb-4 flex items-center justify-between text-white">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Lembur</h2>
            <p class="text-blue-200 text-sm mt-0.5">Pengajuan Overtime</p>
        </div>
        <a href="<?= BASE_URL ?>/pegawai" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 active:scale-90 transition-transform">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
    </header>

    <!-- Konten Scrollable -->
    <div class="relative z-10 px-5 mt-2 space-y-6">

        <!-- Form Pengajuan Lembur -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock text-primary"></i> Form Lembur
            </h3>
            <form id="form-lembur" onsubmit="submitLembur(event)" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Lembur <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mulai Jam <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_mulai" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Selesai Jam <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_selesai" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan Pekerjaan <span class="text-red-500">*</span></label>
                    <textarea name="keterangan" rows="2" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all text-gray-700 placeholder-gray-400" placeholder="Apa yang Anda kerjakan saat lembur?"></textarea>
                </div>

                <button type="submit" id="btn-submit" class="w-full py-3.5 bg-primary text-white rounded-xl font-bold text-sm shadow-[0_8px_20px_-6px_rgba(30,64,175,0.5)] active:scale-95 transition-all mt-2">
                    Kirim Pengajuan
                </button>
            </form>
        </div>

        <!-- Riwayat Lembur -->
        <div>
            <h3 class="font-bold text-gray-800 mb-3 px-1">Riwayat Lembur Saya</h3>
            
            <?php if (empty($riwayat_lembur)): ?>
            <div class="bg-white rounded-2xl p-8 text-center border border-dashed border-gray-200 shadow-sm">
                <i class="fa-solid fa-bed text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm font-semibold text-gray-500">Belum ada riwayat lembur.</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($riwayat_lembur as $l): 
                    $tgl = date('d M Y', strtotime($l['tanggal']));
                    $jam = date('H:i', strtotime($l['jam_mulai'])) . ' - ' . date('H:i', strtotime($l['jam_selesai']));
                    
                    $bg_status = 'bg-gray-100 text-gray-500 border-gray-200'; $icon_status = 'fa-clock'; $text_status = 'Menunggu';
                    if ($l['status'] == 'approved') { $bg_status = 'bg-green-50 text-green-700 border-green-200'; $icon_status = 'fa-check-circle'; $text_status = 'Disetujui'; }
                    elseif ($l['status'] == 'rejected') { $bg_status = 'bg-red-50 text-red-700 border-red-200'; $icon_status = 'fa-times-circle'; $text_status = 'Ditolak'; }
                ?>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-moon text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-gray-800 text-sm"><?= $tgl ?></h4>
                            <span class="px-2 py-0.5 rounded-md border text-[9px] font-bold <?= $bg_status ?> flex items-center gap-1"><i class="fa-solid <?= $icon_status ?>"></i> <?= $text_status ?></span>
                        </div>
                        <p class="text-[11px] font-bold text-indigo-500 mb-1">
                            <i class="fa-regular fa-clock mr-1"></i> <?= $jam ?> (<?= $l['durasi_jam'] ?> Jam)
                        </p>
                        <p class="text-xs text-gray-600 line-clamp-2"><?= htmlspecialchars($l['keterangan']) ?></p>
                        
                        <?php if ($l['status'] == 'rejected' && $l['alasan_reject']): ?>
                            <div class="mt-2 text-[10px] text-red-500 bg-red-50 p-2 rounded-lg border border-red-100">
                                <b>Alasan Ditolak:</b> <?= htmlspecialchars($l['alasan_reject']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    async function submitLembur(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        const form = document.getElementById('form-lembur');
        
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengirim...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/pegawai/ajukan_lembur', {
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
