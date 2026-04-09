<?php
include '../config/db.php';

header('Content-Type: application/json');

$template = $_POST['template'] ?? '';

$data = [];

if($template == 'kopi'){
    $data = [
        ["Kopi Hitam",5000],
        ["Kopi Susu",8000],
        ["Teh Manis",4000],
        ["Indomie Goreng",10000],
        ["Indomie Rebus",10000],
        ["Roti Bakar Coklat",12000],
        ["Roti Bakar Keju",12000],
        ["Susu Hangat",7000],
        ["Air Mineral",3000],
        ["Es Teh",4000],
        ["Es Jeruk",5000],
        ["Americano",15000],
        ["Cappuccino",18000],
        ["Latte",20000],
        ["Matcha",18000],
        ["Coklat Panas",15000],
        ["Kopi Tubruk",6000],
        ["Kopi Susu Gula Aren",12000],
        ["Roti Bakar Mix",15000],
        ["Snack Kentang",10000],
    ];
}

if($template == 'sembako'){
    $data = [
        ["Beras 1kg",15000],
        ["Minyak Goreng",20000],
        ["Gula Pasir",14000],
        ["Telur",25000],
        ["Mie Instan",3000],
        ["Kecap",8000],
        ["Saus",7000],
        ["Tepung Terigu",12000],
        ["Garam",3000],
        ["Sabun",5000],
    ];
}

if($template == 'jajanan'){
    $data = [
        ["Cilok",5000],
        ["Batagor",10000],
        ["Siomay",12000],
        ["Cireng",8000],
        ["Bakso",15000],
        ["Seblak",12000],
        ["Makaroni",7000],
        ["Keripik",6000],
        ["Permen",2000],
        ["Es Pop Ice",5000],
    ];
}

if($template == 'laundry'){
    $data = [
        ["Cuci Kering",5000],
        ["Cuci Setrika",7000],
        ["Setrika Saja",4000],
        ["Cuci Sepatu",25000],
        ["Cuci Karpet",30000],
        ["Cuci Bed Cover",35000],
        ["Laundry Express",10000],
        ["Parfum Laundry",5000],
    ];
}

// INSERT TANPA HAPUS DATA LAMA
foreach($data as $d){
    $nama = $d[0];
    $harga = $d[1];

    // CEK BIAR GAK DUPLIKAT
    $cek = $conn->prepare("SELECT id FROM produk WHERE nama = ?");
    $cek->bind_param("s", $nama);
    $cek->execute();
    $cek->store_result();

    if($cek->num_rows == 0){
        $stmt = $conn->prepare("INSERT INTO produk (nama,harga,stok,min_stok,kategori,aktif) VALUES (?, ?, 10, 0, 'Umum',1)");
        $stmt->bind_param("si", $nama, $harga);
        $stmt->execute();
    }
}

echo json_encode([
    "status" => "ok",
    "message" => "Template berhasil ditambahkan"
]);