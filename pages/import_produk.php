<?php
include '../config/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['import'])){
    $file = $_FILES['file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray();

    foreach($sheet as $index => $row){
        if($index == 0) continue; // skip header

        $nama = $row[0];
        $harga = $row[1];
        $stok = $row[2];

        $conn->query("INSERT INTO produk(nama,harga,stok)
        VALUES('$nama','$harga','$stok')");
    }

    echo "Import berhasil!";
}
?>

<h3>Import Produk (Excel)</h3>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button name="import">Import</button>
</form>