<?php
session_start();
include '../config/db.php';

if(isset($_POST['tambah_stok'])){
    $id = $_POST['id_produk'];
    $tambah = (int)$_POST['qty_tambah'];

    $stmt = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
    $stmt->bind_param("ii", $tambah, $id);
    $stmt->execute();

    header("Location: stok.php");
    exit();
}

$produk_query = $conn->query("SELECT id, nama, stok, min_stok FROM produk ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stok - Kasir POS</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #10b981;
    --primary-light: #d1fae5;
    --primary-dark: #059669;
    --bg-color: #f9fafb;
    --card-bg: #ffffff;
    --text-main: #1f2937;
    --text-muted: #6b7280;
    --border-color: #f3f4f6;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{background:var(--bg-color);color:var(--text-main);padding-bottom:120px;}

.header{
    display:flex;
    justify-content:center;
    align-items:center;
    padding:1.2rem;
    background:var(--card-bg);
}

.container{
    padding:1rem;
    max-width:600px;
    margin:auto;
}

/* ITEM */
.stock-item{
    background:var(--card-bg);
    border-radius:14px;
    padding:1rem;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border:1px solid var(--border-color);
    margin-bottom:0.8rem;
}

.stock-info h4{
    font-size:0.9rem;
    font-weight:600;
    margin-bottom:4px;
}

/* BADGE */
.badge-stok{
    font-size:0.75rem;
    font-weight:700;
    padding:0.25rem 0.6rem;
    border-radius:6px;
    background:var(--primary-light);
    color:var(--primary-dark);
}

.badge-warning{
    background:#fee2e2;
    color:#b91c1c;
}

/* 🔥 STOK HABIS */
.badge-habis{
    background:#dc2626;
    color:white;
}

.text-habis{
    font-size:11px;
    color:#dc2626;
    font-weight:600;
    margin-top:4px;
}

/* FORM */
.stock-form{
    display:flex;
    gap:5px;
    align-items:center;
}

.stock-form input{
    width:60px;
    padding:0.5rem;
    border:1px solid #e5e7eb;
    border-radius:6px;
    text-align:center;
}

.stock-form button{
    background:var(--primary);
    color:white;
    border:none;
    padding:0.5rem 0.8rem;
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
}

/* NAV */
.bottom-nav{
    position:fixed;
    bottom:0;
    width:100%;
    max-width:600px;
    left:50%;
    transform:translateX(-50%);
    background:white;
    display:flex;
    justify-content:space-around;
    padding:10px 0;
    box-shadow:0 -4px 12px rgba(0,0,0,0.08);
    border-top-left-radius:16px;
    border-top-right-radius:16px;
}

.bottom-nav a{
    text-align:center;
    text-decoration:none;
    color:#9ca3af;
    font-size:11px;
    flex:1;
}

.bottom-nav a.active{
    color:#10b981;
    font-weight:600;
}

.nav-icon{
    display:block;
    font-size:18px;
    margin-bottom:4px;
}
</style>
</head>

<body>

<div class="header"><h2>Inventaris Stok</h2></div>

<div class="container">

<?php if($produk_query->num_rows == 0): ?>

    <div class="empty-card">
        <div class="empty-icon">📦</div>

        <h3>Belum ada produk</h3>
        <p>Tambahkan produk dulu atau pakai template</p>

        <div class="empty-actions">
            <a href="produk.php" class="btn-outline">+ Tambah Produk</a>
        </div>
    </div>

<?php else: ?>

<?php while($p = $produk_query->fetch_assoc()): ?>

<?php 
$isHabis = ($p['stok'] == 0);
$isKritis = ($p['stok'] <= $p['min_stok'] && !$isHabis);

if($isHabis){
    $badgeClass = 'badge-habis';
}elseif($isKritis){
    $badgeClass = 'badge-stok badge-warning';
}else{
    $badgeClass = 'badge-stok';
}
?>

<div class="stock-item">

<div class="stock-info">
    <h4><?= htmlspecialchars($p['nama']) ?></h4>

    <span class="<?= $badgeClass ?>">
        <?= $isHabis ? 'Stok Habis' : 'Sisa: '.$p['stok'] ?>
    </span>

    <?php if($isHabis): ?>
        <div class="text-habis">
            ⚠️ Stok kosong, silahkan tambahkan di bagian produk
        </div>
    <?php endif; ?>
</div>

<form method="POST" class="stock-form">
    <input type="hidden" name="id_produk" value="<?= $p['id'] ?>">
    <input type="number" name="qty_tambah" placeholder="+Qty" required min="1">
    <button type="submit" name="tambah_stok">+</button>
</form>

</div>

<?php endwhile; ?>

<?php endif; ?>

</div>

<!-- NAV -->
<div class="bottom-nav">
<a href="kasir.php"><span class="nav-icon">🖩</span>Kasir</a>
<a href="produk.php"><span class="nav-icon">📦</span>Produk</a>
<a href="stok.php" class="active"><span class="nav-icon">📋</span>Stok</a>
<a href="laporan.php"><span class="nav-icon">📄</span>Laporan</a>
<a href="pengaturan.php"><span class="nav-icon">⚙️</span>Setelan</a>
</div>

</body>
</html>