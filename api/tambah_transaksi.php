<?php
include '../config/db.php';
include '../config/env.php';

$data = json_decode(file_get_contents("php://input"), true);

$cart = $data['cart'];
$bayar = $data['bayar'];
$metode = $data['metode'];

// ========================
// HITUNG TOTAL
// ========================
$total = 0;
foreach($cart as $item){
    $total += $item['harga'] * $item['qty'];
}

// ========================
// SIMPAN TRANSAKSI
// ========================
$kode = "TRX-" . time();

$conn->query("INSERT INTO transaksi(kode,total,bayar,metode) 
VALUES('$kode','$total','$bayar','$metode')");

$trx_id = $conn->insert_id;

// ========================
// LOOP UTAMA (DB + WA + STOK ALERT)
// ========================
$pesan = "🏪 *LitPOS Mart Kemang*\n\n";

foreach($cart as $item){

    // SIMPAN DETAIL
    $conn->query("INSERT INTO transaksi_detail(transaksi_id,produk_id,qty,harga)
    VALUES('$trx_id','{$item['id']}','{$item['qty']}','{$item['harga']}')");

    // UPDATE STOK
    $conn->query("UPDATE produk SET stok = stok - {$item['qty']} WHERE id={$item['id']}");

    // AMBIL DATA TERBARU
    $result = $conn->query("SELECT nama, stok, min_stok FROM produk WHERE id={$item['id']}");
    $produk = $result->fetch_assoc();

    // TAMBAH KE PESAN
    $pesan .= "- {$produk['nama']} ({$item['qty']}x)\n";

    // 🔥 STOK MENIPIS
    if($produk['stok'] <= $produk['min_stok']){
        $pesan .= "⚠️ {$produk['nama']} hampir habis (sisa {$produk['stok']})\n";
    }
}

// ========================
// TAMBAHAN INFO
// ========================
$pesan .= "\n💰 Total: Rp " . number_format($total,0,',','.');
$pesan .= "\n💵 Bayar: Rp " . number_format($bayar,0,',','.');
$pesan .= "\n📌 Metode: $metode";
$pesan .= "\n⏰ " . date("d-m-Y H:i");

// ========================
// KIRIM WA (FONNTE)
// ========================
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => array(
        'target' => FONNTE_TARGET,
        'message' => $pesan,
    ),
    CURLOPT_HTTPHEADER => array(
        "Authorization: " . FONNTE_TOKEN
    ),
));

curl_exec($curl);
curl_close($curl);

// ========================
echo json_encode(["status"=>"ok"]);