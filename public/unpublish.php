<?php
// Script Unpublish Gaji Manual
require_once dirname(__DIR__) . '/app/Config/database.php';

$message = "";

if (isset($_POST['unpublish'])) {
    $bulan = (int)$_POST['bulan'];
    $tahun = (int)$_POST['tahun'];

    $db = (new Database())->getConnection();

    try {
        $db->beginTransaction();

        // 1. Hapus kas denda cabang terkait
        $stmt1 = $db->prepare("
            DELETE FROM kas_denda_cabang 
            WHERE id_penggajian IN (
                SELECT id_gaji FROM penggajian_bulanan 
                WHERE bulan = :bulan AND tahun = :tahun AND status = 'published'
            )
        ");
        $stmt1->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt1->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt1->execute();
        $deletedKas = $stmt1->rowCount();

        // 2. Kembalikan status menjadi draft
        $stmt2 = $db->prepare("
            UPDATE penggajian_bulanan 
            SET status = 'draft' 
            WHERE bulan = :bulan AND tahun = :tahun AND status = 'published'
        ");
        $stmt2->bindParam(':bulan', $bulan, PDO::PARAM_INT);
        $stmt2->bindParam(':tahun', $tahun, PDO::PARAM_INT);
        $stmt2->execute();
        $updatedDraft = $stmt2->rowCount();

        $db->commit();
        
        if ($updatedDraft > 0) {
            $message = "<div style='color: green; font-weight: bold;'>Berhasil unpublish Gaji Bulan $bulan Tahun $tahun!<br>Data kas denda dihapus: $deletedKas<br>Status gaji dikembalikan ke draft: $updatedDraft</div>";
        } else {
            $message = "<div style='color: #d35400; font-weight: bold;'>Tidak ada data gaji yang berstatus published pada Bulan $bulan Tahun $tahun.</div>";
        }

    } catch (Exception $e) {
        $db->rollBack();
        $message = "<div style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unpublish Gaji Manual | PT REN</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f4f7f6; }
        .container { max-width: 450px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); margin: auto; }
        h2 { color: #2c3e50; margin-top: 0; text-align: center; margin-bottom: 10px;}
        p.desc { text-align: center; color: #7f8c8d; font-size: 14px; margin-bottom: 25px; }
        label { font-weight: 600; display: block; margin-top: 15px; color: #34495e; font-size: 14px; }
        input { width: 100%; padding: 12px; margin-top: 8px; border: 1px solid #dcdde1; border-radius: 6px; box-sizing: border-box; font-size: 16px; transition: border-color 0.3s; }
        input:focus { border-color: #3498db; outline: none; }
        button { margin-top: 25px; width: 100%; padding: 14px; background: #e74c3c; color: white; border: none; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #c0392b; }
        .alert { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #3498db; border-radius: 4px; font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Alat Darurat (Unpublish Gaji)</h2>
        <p class="desc">Membatalkan publikasi gaji dan mengembalikan dana denda kas yang terlanjur tercatat.</p>
        
        <?php if ($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirm('Anda yakin ingin melakukan unpublish? Gaji bulan tersebut akan kembali berstatus Draft dan kas denda yang tercatat akan dihapus.');">
            <label>Bulan (1 - 12)</label>
            <input type="number" name="bulan" min="1" max="12" value="<?= date('n') ?>" required>

            <label>Tahun</label>
            <input type="number" name="tahun" min="2020" max="2100" value="<?= date('Y') ?>" required>

            <button type="submit" name="unpublish">Tarik Kembali (Unpublish)</button>
        </form>
    </div>
</body>
</html>
