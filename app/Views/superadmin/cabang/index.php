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
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nilai UMK Daerah (Rp) <span class="text-gray-400 font-normal">(Opsional)</span></label>
            <input name="umk" id="<?= $prefix ?>umk" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0">
            <p class="text-[10px] text-gray-400 mt-1">Digunakan sebagai Basis Potongan BPJS & Pensiun. Biarkan kosong untuk gunakan total gaji & tunjangan.</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Zona Waktu (Timezone) *</label>
            <select name="timezone" id="<?= $prefix ?>timezone" required class="<?= $inputClass ?> select-modern">
                <option value="Asia/Jakarta">WIB — Waktu Indonesia Barat (UTC+7) — Jawa, Sumatera, Kalimantan Barat & Tengah</option>
                <option value="Asia/Makassar">WITA — Waktu Indonesia Tengah (UTC+8) — Bali, NTB, NTT, Sulawesi, Kalimantan Timur</option>
                <option value="Asia/Jayapura">WIT — Waktu Indonesia Timur (UTC+9) — Papua, Maluku</option>
            </select>
        </div>
        <div class="grid grid-cols-3 gap-4 items-end">
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
        <div class="grid grid-cols-3 gap-3 items-end">
            <div><label class="block text-xs text-gray-500 mb-1">1-5 menit</label><input name="denda_1_5" id="<?= $prefix ?>denda_1_5" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">6-10 menit</label><input name="denda_6_10" id="<?= $prefix ?>denda_6_10" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">11-15 menit</label><input name="denda_11_15" id="<?= $prefix ?>denda_11_15" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">16-30 menit</label><input name="denda_16_30" id="<?= $prefix ?>denda_16_30" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
            <div><label class="block text-xs text-gray-500 mb-1">31-60 menit</label><input name="denda_31_60" id="<?= $prefix ?>denda_31_60" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
            <div><label class="block text-xs text-red-500 mb-1">Alfa (Rp)</label><input name="denda_alfa" id="<?= $prefix ?>denda_alfa" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
            <div class="col-span-3"><label class="block text-xs text-orange-500 mb-1">Tidak Absen Pulang (Rp)</label><input name="denda_tidak_absen_pulang" id="<?= $prefix ?>denda_tidak_absen_pulang" type="text" class="<?= $inputClass ?> currency-input" placeholder="Rp. 0"></div>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>

<style>
.select-modern {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.2em 1.2em;
    padding-right: 2.5rem !important;
}
</style>
<script>
    // ================== CURRENCY FORMATTER ==================
    function formatRupiah(value) {
        if (value === null || value === undefined || value === '') return '';
        let number_string = value.toString().replace(/[^,\d]/g, ''),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah ? 'Rp. ' + rupiah : '';
    }

    function cleanRupiah(str) {
        return str.toString().replace(/[^0-9]/g, '');
    }

    document.querySelectorAll('.currency-input').forEach(input => {
        if(input.value && input.value !== '0' && !input.value.startsWith('Rp')) {
            input.value = formatRupiah(input.value);
        }
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    });
    // ========================================================

    function bukaModalTambah() {
        document.getElementById('form-tambah').reset();
        document.getElementById('modal-tambah').classList.remove('hidden');
        document.getElementById('modal-tambah').classList.add('flex');
    }
    function bukaModalEdit(d) {
        document.getElementById('edit-id_cabang').value    = d.id_cabang;
        document.getElementById('edit-nama_cabang').value  = d.nama_cabang;
        document.getElementById('edit-umk').value          = d.umk ? formatRupiah(parseInt(d.umk, 10)) : '';
        document.getElementById('edit-timezone').value     = d.timezone || 'Asia/Jakarta';
        document.getElementById('edit-latitude').value     = d.latitude;
        document.getElementById('edit-longitude').value    = d.longitude;
        document.getElementById('edit-radius_meter').value = d.radius_meter;
        
        document.getElementById('edit-denda_1_5').value    = d.denda_1_5 ? formatRupiah(parseInt(d.denda_1_5, 10)) : '';
        document.getElementById('edit-denda_6_10').value   = d.denda_6_10 ? formatRupiah(parseInt(d.denda_6_10, 10)) : '';
        document.getElementById('edit-denda_11_15').value  = d.denda_11_15 ? formatRupiah(parseInt(d.denda_11_15, 10)) : '';
        document.getElementById('edit-denda_16_30').value  = d.denda_16_30 ? formatRupiah(parseInt(d.denda_16_30, 10)) : '';
        document.getElementById('edit-denda_31_60').value  = d.denda_31_60 ? formatRupiah(parseInt(d.denda_31_60, 10)) : '';
        document.getElementById('edit-denda_alfa').value   = d.denda_alfa ? formatRupiah(parseInt(d.denda_alfa, 10)) : '';
        document.getElementById('edit-denda_tidak_absen_pulang').value = d.denda_tidak_absen_pulang ? formatRupiah(parseInt(d.denda_tidak_absen_pulang, 10)) : '';
        
        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('modal-edit').classList.add('flex');
    }
    function tutupModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    async function submitTambah(e) {
        e.preventDefault();
        const form = document.getElementById('form-tambah');
        form.querySelectorAll('.currency-input').forEach(input => { input.dataset.raw = input.value; input.value = cleanRupiah(input.value); });
        
        const resp = await fetch('<?= BASE_URL ?>/superadmin/simpan_cabang', { method: 'POST', body: new FormData(form) });
        
        form.querySelectorAll('.currency-input').forEach(input => { input.value = input.dataset.raw; });
        const data = await resp.json();
        if (data.status === 'success') Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        else Swal.fire('Gagal', data.message, 'error');
    }
    
    async function submitEdit(e) {
        e.preventDefault();
        const form = document.getElementById('form-edit');
        form.querySelectorAll('.currency-input').forEach(input => { input.dataset.raw = input.value; input.value = cleanRupiah(input.value); });
        
        const resp = await fetch('<?= BASE_URL ?>/superadmin/update_cabang', { method: 'POST', body: new FormData(form) });
        
        form.querySelectorAll('.currency-input').forEach(input => { input.value = input.dataset.raw; });
        const data = await resp.json();
        if (data.status === 'success') Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
        else Swal.fire('Gagal', data.message, 'error');
    }
</script>
