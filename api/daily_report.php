<?php
include '../config/db.php';
include '../config/env.php';

// =================
// HITUNG DATA
// =================
$today = date('Y-m-d');

// omzet + trx
$q = $conn->query("
    SELECT SUM(total) as omzet, COUNT(id) as trx 
    FROM transaksi 
    WHERE DATE(created_at)='$today'
");
$d = $q->fetch_assoc();

$omzet = $d['omzet'] ?? 0;
$trx = $d['trx'] ?? 0;

// produk terlaris
$top = $conn->query("
    SELECT p.nama, SUM(td.qty) as total 
    FROM transaksi_detail td
    JOIN produk p ON p.id = td.produk_id
    WHERE DATE(td.id)
    GROUP BY p.id
    ORDER BY total DESC
    LIMIT 1
");

$t = $top->fetch_assoc();
$top_produk = $t ? "{$t['nama']} ({$t['total']}x)" : "-";

// stok menipis
$low = $conn->query("
    SELECT nama, stok FROM produk 
    WHERE stok <= min_stok
");

$low_text = "";
while($l = $low->fetch_assoc()){
    $low_text .= "- {$l['nama']} ({$l['stok']})\n";
}

if(!$low_text){
    $low_text = "Aman semua 👍";
}

// =================
// FORMAT PESAN
// =================
$msg = "📊 *Laporan Harian*\n\n";
$msg .= "💰 Omzet: Rp ".number_format($omzet,0,',','.')."\n";
$msg .= "🧾 Transaksi: $trx\n";
$msg .= "🔥 Terlaris: $top_produk\n\n";
$msg .= "⚠️ Stok Menipis:\n$low_text";

// =================
// KIRIM FONNTE
// =================
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'target' => FONNTE_TARGET,
        'message' => $msg,
    ],
    CURLOPT_HTTPHEADER => [
        "Authorization: ".FONNTE_TOKEN
    ],
]);

curl_exec($curl);
curl_close($curl);

echo "OK";