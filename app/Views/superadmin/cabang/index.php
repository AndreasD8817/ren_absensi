<!-- Halaman Kelola Cabang - Superadmin -->
<div class="flex h-screen overflow-hidden bg-gray-50">

    <?php include_once APP_PATH . '/Views/superadmin/_sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Cabang</h2>
                <p class="text-gray-500 text-sm mt-1">Atur lokasi, radius absen, dan nominal denda per cabang</p>
            </div>
            <button onclick="bukaModalTambah()" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md hover:bg-blue-800 active:scale-95 transition-all">
                <i class="fa-solid fa-plus"></i> Tambah Cabang
            </button>
        </div>

        <!-- Grid Kartu Cabang -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php foreach ($cabang as $c): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="bg-gradient-to-r from-primary to-blue-600 p-5 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg"><?= esc($c['nama_cabang']) ?></h3>
                            <p class="text-blue-200 text-xs mt-1">Radius: <?= $c['radius_meter'] ?> meter</p>
                        </div>
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-building text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-blue-200">
                        <i class="fa-solid fa-location-dot"></i>
                        <span class="font-mono"><?= $c['latitude'] ?>, <?= $c['longitude'] ?></span>
                    </div>
                </div>
                <div class="p-5">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Denda Keterlambatan</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-[10px] text-gray-400">1-5 menit</p>
                            <p class="font-semibold text-gray-700">Rp <?= number_format($c['denda_1_5'],0,',','.') ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-[10px] text-gray-400">6-10 menit</p>
                            <p class="font-semibold text-gray-700">Rp <?= number_format($c['denda_6_10'],0,',','.') ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-[10px] text-gray-400">11-15 menit</p>
                            <p class="font-semibold text-gray-700">Rp <?= number_format($c['denda_11_15'],0,',','.') ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-[10px] text-gray-400">16-30 menit</p>
                            <p class="font-semibold text-gray-700">Rp <?= number_format($c['denda_16_30'],0,',','.') ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-[10px] text-gray-400">31-60 menit</p>
                            <p class="font-semibold text-gray-700">Rp <?= number_format($c['denda_31_60'],0,',','.') ?></p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-2">
                            <p class="text-[10px] text-red-400">Alfa / Mangkir</p>
                            <p class="font-semibold text-red-600">Rp <?= number_format($c['denda_alfa'],0,',','.') ?></p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-2 col-span-2">
                            <p class="text-[10px] text-orange-500">Tidak Absen Pulang</p>
                            <p class="font-semibold text-orange-600">Rp <?= number_format($c['denda_tidak_absen_pulang'],0,',','.') ?></p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-end items-center">
                        <button onclick='bukaModalEdit(<?= json_encode($c) ?>)' class="flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-blue-800 transition-colors">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Modal Tambah Cabang -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-building-circle-check text-primary mr-2"></i>Tambah Cabang Baru</h3>
            <button onclick="tutupModal('modal-tambah')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-tambah" onsubmit="submitTambah(event)" class="p-6 space-y-4">
    <?= csrf_field() ?>
            <?php echo renderFormCabang(); ?>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-tambah')" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold hover:bg-blue-800">Simpan Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Cabang -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-pen text-blue-500 mr-2"></i>Edit Cabang</h3>
            <button onclick="tutupModal('modal-edit')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form id="form-edit" onsubmit="submitEdit(event)" class="p-6 space-y-4">
    <?= csrf_field() ?>
            <input type="hidden" name="id_cabang" id="edit-id_cabang">
            <?php echo renderFormCabang('edit-'); ?>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-edit')" class="px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold hover:bg-blue-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
function renderFormCabang($prefix = '') {
    $inputClass = "w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none";
    ob_start(); ?>
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Cabang *</label>
            <input name="nama_cabang" id="<?= $prefix ?>nama_cabang" required class="<?= $inputClass ?>" placeholder="Contoh: Kantor Pusat Jakarta">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Zona Waktu (Timezone) *</label>
            <select name="timezone" id="<?= $prefix ?>timezone" required class="<?= $inputClass ?>">
                <option value="Asia/Jakarta">WIB — Waktu Indonesia Barat (UTC+7) — Jawa, Sumatera, Kalimantan Barat & Tengah</option>
                <option value="Asia/Makassar">WITA — Waktu Indonesia Tengah (UTC+8) — Bali, NTB, NTT, Sulawesi, Kalimantan Timur</option>
                <option value="Asia/Jayapura">WIT — Waktu Indonesia Timur (UTC+9) — Papua, Maluku</option>
            </select>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Latitude *</label>
                <input name="latitude" id="<?= $prefix ?>latitude" required class="<?= $inputClass ?>" placeholder="-7.2875">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Longitude *</label>
                <input name="longitude" id="<?= $prefix ?>longitude" required class="<?= $inputClass ?>" placeholder="112.7521">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Radius (meter) *</label>
                <input name="radius_meter" id="<?= $prefix ?>radius_meter" required type="number" class="<?= $inputClass ?>" placeholder="100">
            </div>
        </div>
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider pt-2 border-t">Nominal Denda Keterlambatan & Kehadiran (Rp)</p>
        <div class="grid grid-cols-3 gap-3">
            <div><label class="block text-xs text-gray-500 mb-1">1-5 menit</label><input name="denda_1_5" id="<?= $prefix ?>denda_1_5" type="number" class="<?= $inputClass ?>" placeholder="0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">6-10 menit</label><input name="denda_6_10" id="<?= $prefix ?>denda_6_10" type="number" class="<?= $inputClass ?>" placeholder="0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">11-15 menit</label><input name="denda_11_15" id="<?= $prefix ?>denda_11_15" type="number" class="<?= $inputClass ?>" placeholder="0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">16-30 menit</label><input name="denda_16_30" id="<?= $prefix ?>denda_16_30" type="number" class="<?= $inputClass ?>" placeholder="0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">31-60 menit</label><input name="denda_31_60" id="<?= $prefix ?>denda_31_60" type="number" class="<?= $inputClass ?>" placeholder="0"></div>
            <div><label class="block text-xs text-red-500 mb-1">Alfa (Rp)</label><input name="denda_alfa" id="<?= $prefix ?>denda_alfa" type="number" class="<?= $inputClass ?>" placeholder="50000"></div>
            <div class="col-span-3"><label class="block text-xs text-orange-500 mb-1">Tidak Absen Pulang (Rp)</label><input name="denda_tidak_absen_pulang" id="<?= $prefix ?>denda_tidak_absen_pulang" type="number" class="<?= $inputClass ?>" placeholder="0"></div>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>

<script>
    function bukaModalTambah() {
        document.getElementById('form-tambah').reset();
        document.getElementById('modal-tambah').classList.remove('hidden');
        document.getElementById('modal-tambah').classList.add('flex');
    }
    function bukaModalEdit(d) {
        document.getElementById('edit-id_cabang').value    = d.id_cabang;
        document.getElementById('edit-nama_cabang').value  = d.nama_cabang;
        document.getElementById('edit-timezone').value     = d.timezone || 'Asia/Jakarta';
        document.getElementById('edit-latitude').value     = d.latitude;
        document.getElementById('edit-longitude').value    = d.longitude;
        document.getElementById('edit-radius_meter').value = d.radius_meter;
        document.getElementById('edit-denda_1_5').value    = d.denda_1_5;
        document.getElementById('edit-denda_6_10').value   = d.denda_6_10;
        document.getElementById('edit-denda_11_15').value  = d.denda_11_15;
        document.getElementById('edit-denda_16_30').value  = d.denda_16_30;
        document.getElementById('edit-denda_31_60').value  = d.denda_31_60;
        document.getElementById('edit-denda_alfa').value   = d.denda_alfa;
        document.getElementById('edit-denda_tidak_absen_pulang').value = d.denda_tidak_absen_pulang;
        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('modal-edit').classList.add('flex');
    }
    function tutupModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    async function submitTambah(e) {
        e.preventDefault();
        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_cabang', { method: 'POST', body: new FormData(document.getElementById('form-tambah')) });
        const data = await resp.json();
        if (data.status === 'success') Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        else Swal.fire('Gagal', data.message, 'error');
    }
    async function submitEdit(e) {
        e.preventDefault();
        const resp = await fetch('<?= BASE_URL ?>/superadmin/update_cabang', { method: 'POST', body: new FormData(document.getElementById('form-edit')) });
        const data = await resp.json();
        if (data.status === 'success') Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        else Swal.fire('Gagal', data.message, 'error');
    }
</script>
