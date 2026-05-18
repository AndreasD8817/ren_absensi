<!-- Halaman Pengaturan Cabang - Admin Cabang -->
<div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
    
    <?php include_once APP_PATH . '/Views/admin_cabang/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <header class="mb-8 border-b border-gray-200 pb-4">
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Cabang</h2>
            <p class="text-gray-500 text-sm mt-1">Konfigurasi nilai denda khusus untuk <?= htmlspecialchars($cabang['nama_cabang']) ?></p>
        </header>

        <!-- Peringatan -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-8 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-info text-blue-500 text-xl mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold text-blue-800">Informasi Akses</p>
                <p class="text-xs text-blue-700 mt-1">Sebagai Admin Cabang, Anda diberikan wewenang untuk mengatur nilai denda keterlambatan dan denda alfa di cabang Anda. Namun, untuk menjaga integritas sistem absen, pengaturan <b>Titik Koordinat (GPS) dan Radius</b> dikunci dan hanya dapat diubah oleh Pusat (Superadmin).</p>
            </div>
        </div>

        <form id="form-pengaturan" onsubmit="simpanPengaturan(event)" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 max-w-4xl">
            
            <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2"><i class="fa-solid fa-map-location-dot text-gray-400 mr-2"></i>Informasi Lokasi (Read-Only)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Cabang</label>
                    <input type="text" value="<?= htmlspecialchars($cabang['nama_cabang']) ?>" readonly class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 text-sm cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Koordinat (Lat, Long)</label>
                    <input type="text" value="<?= $cabang['latitude'] ?>, <?= $cabang['longitude'] ?>" readonly class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 text-sm cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Radius Absensi</label>
                    <div class="relative">
                        <input type="text" value="<?= $cabang['radius_meter'] ?>" readonly class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 text-sm cursor-not-allowed">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">Meter</span>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2"><i class="fa-solid fa-money-bill-wave text-orange-500 mr-2"></i>Pengaturan Denda & Lembur (Dapat Diubah)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-xs font-bold text-orange-700 mb-1">Telat 1-5 Menit</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="denda_1_5" value="<?= $cabang['denda_1_5'] ?>" required class="w-full pl-10 pr-3 py-2 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm transition-all shadow-sm"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-orange-700 mb-1">Telat 6-10 Menit</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="denda_6_10" value="<?= $cabang['denda_6_10'] ?>" required class="w-full pl-10 pr-3 py-2 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm transition-all shadow-sm"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-orange-700 mb-1">Telat 11-15 Menit</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="denda_11_15" value="<?= $cabang['denda_11_15'] ?>" required class="w-full pl-10 pr-3 py-2 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm transition-all shadow-sm"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-orange-700 mb-1">Telat 16-30 Menit</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="denda_16_30" value="<?= $cabang['denda_16_30'] ?>" required class="w-full pl-10 pr-3 py-2 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm transition-all shadow-sm"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-orange-700 mb-1">Telat > 30 Menit</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="denda_31_60" value="<?= $cabang['denda_31_60'] ?>" required class="w-full pl-10 pr-3 py-2 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm transition-all shadow-sm"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 bg-orange-50 border border-orange-200 rounded-2xl mb-8">
                <div>
                    <label class="block text-xs font-bold text-red-700 mb-1">Denda Tidak Hadir (Alfa)</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-red-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="denda_alfa" value="<?= $cabang['denda_alfa'] ?>" required class="w-full pl-10 pr-3 py-2 border border-red-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none text-sm bg-white"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-blue-700 mb-1">Tarif Lembur (Per Jam)</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 text-sm font-semibold">Rp</span>
                    <input type="number" name="tarif_lembur_per_jam" value="<?= $cabang['tarif_lembur_per_jam'] ?>" required class="w-full pl-10 pr-3 py-2 border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white"></div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" id="btn-submit" class="px-8 py-3 bg-[#431407] hover:bg-[#7c2d12] text-white rounded-xl font-bold shadow-[0_8px_20px_-6px_rgba(124,45,18,0.5)] transition-all flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>

    </main>
</div>

<script>
    async function simpanPengaturan(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        const resp = await fetch('<?= BASE_URL ?>/admincabang/simpan_pengaturan', {
            method: 'POST',
            body: new FormData(e.target)
        });
        const data = await resp.json();
        
        btn.innerHTML = '<i class="fa-solid fa-save"></i> Simpan Perubahan';
        btn.disabled = false;

        if (data.status === 'success') {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Pengaturan denda cabang berhasil diperbarui.',
                icon: 'success',
                confirmButtonColor: '#7c2d12'
            });
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    }
</script>
