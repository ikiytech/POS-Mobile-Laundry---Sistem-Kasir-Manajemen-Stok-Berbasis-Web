<?php
session_start();
include '../config/db.php';

// ========================
// TAMBAH PRODUK
// ========================
if(isset($_POST['simpan'])){
    $nama = trim($_POST['nama']);
    $kategori = trim($_POST['kategori']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $min_stok = $_POST['min_stok'];
    
    $stmt = $conn->prepare("INSERT INTO produk (nama, harga, stok, min_stok, kategori, aktif) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("siiis", $nama, $harga, $stok, $min_stok, $kategori);
    $stmt->execute();

    header("Location: produk.php");
    exit();
}

// ========================
// DELETE PRODUK
// ========================
if(isset($_POST['hapus'])){
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: produk.php");
    exit();
}

$produk_query = $conn->query("SELECT * FROM produk ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produk - Kasir POS</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary:#10b981;
    --bg:#f9fafb;
    --card:#ffffff;
    --text:#1f2937;
    --muted:#6b7280;
    --border:#f3f4f6;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{background:var(--bg);padding-bottom:120px;}

.header{
    padding:16px;
    text-align:center;
    font-weight:700;
    background:white;
}

.container{
    padding:16px;
    max-width:600px;
    margin:auto;
}

.card{
    background:white;
    border-radius:14px;
    padding:16px;
    margin-bottom:16px;
    border:1px solid var(--border);
}

.btn-template{
    background:#facc15;
    padding:12px;
    border:none;
    border-radius:12px;
    width:100%;
    font-weight:600;
    margin-bottom:12px;
    cursor:pointer;
}

.form-control{
    width:100%;
    padding:10px;
    border:1px solid #e5e7eb;
    border-radius:10px;
    margin-bottom:10px;
}

.flex-row{display:flex;gap:10px;}

.btn-primary{
    background:var(--primary);
    color:white;
    border:none;
    padding:12px;
    border-radius:12px;
    width:100%;
}

.list-item{
    display:flex;
    justify-content:space-between;
    padding:14px;
    background:white;
    border-radius:14px;
    margin-bottom:10px;
}

/* MODAL */
.modal{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content{
    background:white;
    padding:20px;
    border-radius:16px;
    width:90%;
    max-width:320px;
    text-align:center;
}

.btn-modal{
    width:100%;
    padding:12px;
    margin-top:8px;
    border:none;
    border-radius:10px;
    background:#f3f4f6;
    font-weight:600;
}

.btn-danger{
    background:#ef4444;
    color:white;
    border:none;
    padding:10px 14px;
    border-radius:10px;
    font-weight:600;
}

.btn-cancel{
    background:#e5e7eb;
    border:none;
    padding:10px;
    border-radius:10px;
}

.modal-actions{
    display:flex;
    gap:10px;
    margin-top:15px;
}

/* NAV (JANGAN DIUBAH LAGI) */
.bottom-nav {
    position: fixed;
    bottom: 0;
    width: 100%;
    max-width: 600px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    display: flex;
    justify-content: space-around;
    padding: 10px 0;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.08);
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}

.bottom-nav a {
    text-align: center;
    text-decoration: none;
    color: #9ca3af;
    font-size: 11px;
    flex: 1;
}

.bottom-nav a.active {
    color: #10b981;
    font-weight: 600;
}

.nav-icon {
    display: block;
    font-size: 18px;
    margin-bottom: 4px;
}
.nav-icon {
    font-size: 1.4rem;
}
</style>
</head>

<body>

<div class="header">Manajemen Produk</div>

<div class="container">

<div class="card">

<button class="btn-template" onclick="openTemplate()">⚡ Pakai Template</button>

<form method="POST">
<input type="text" name="nama" class="form-control" placeholder="Nama Produk" required>

<div class="flex-row">
<input type="number" name="harga" class="form-control" placeholder="Harga" required>
<input type="text" name="kategori" class="form-control" placeholder="Kategori" required>
</div>

<div class="flex-row">
<input type="number" name="stok" class="form-control" placeholder="Stok" required>
<input type="number" name="min_stok" class="form-control" value="0">
</div>

<button name="simpan" class="btn-primary">Simpan Produk</button>
</form>

</div>

<?php while($p=$produk_query->fetch_assoc()): ?>
<div class="list-item">
<div>
<b><?= $p['nama'] ?></b><br>
<small><?= $p['kategori'] ?> • Stok: <?= $p['stok'] ?></small>
</div>

<div style="text-align:right">
<div>Rp <?= number_format($p['harga'],0,',','.') ?></div>

<button onclick="openDelete(<?= $p['id'] ?>)" style="
margin-top:5px;
background:#ef4444;
color:white;
border:none;
padding:6px 10px;
border-radius:8px;
font-size:12px;
cursor:pointer;">
🗑 Hapus
</button>

</div>
</div>
<?php endwhile; ?>

</div>

<!-- MODAL TEMPLATE -->
<div id="modalTemplate" class="modal">
  <div class="modal-content">
    <h3>Pilih Template</h3>
    <button onclick="selectTemplate('kopi')" class="btn-modal">☕ Kopi</button>
    <button onclick="selectTemplate('sembako')" class="btn-modal">🛒 Sembako</button>
    <button onclick="selectTemplate('jajanan')" class="btn-modal">🍟 Jajanan</button>
    <button onclick="selectTemplate('laundry')" class="btn-modal">🧺 Laundry</button>
    <button onclick="closeModal()" class="btn-cancel">Batal</button>
  </div>
</div>

<!-- MODAL CONFIRM -->
<div id="modalConfirm" class="modal">
  <div class="modal-content">
    <h3>Yakin pakai template?</h3>
    <p>Data lama tidak dihapus</p>
    <div class="modal-actions">
      <button onclick="applyTemplate()" class="btn-primary">Ya</button>
      <button onclick="closeConfirm()" class="btn-cancel">Batal</button>
    </div>
  </div>
</div>

<!-- MODAL DELETE -->
<div id="modalDelete" class="modal">
  <div class="modal-content">
    <h3>Hapus Produk?</h3>
    <p>Produk akan dihapus permanen</p>

    <div class="modal-actions">
      <button onclick="confirmDelete()" class="btn-danger">Hapus</button>
      <button onclick="closeDelete()" class="btn-cancel">Batal</button>
    </div>
  </div>
</div>

<!-- FORM DELETE -->
<form id="deleteForm" method="POST">
<input type="hidden" name="id" id="deleteId">
<input type="hidden" name="hapus" value="1">
</form>

<script>
let selectedTemplate = '';
let deleteId = null;

function openTemplate(){
    document.getElementById('modalTemplate').style.display='flex';
}

function closeModal(){
    document.getElementById('modalTemplate').style.display='none';
}

function selectTemplate(type){
    selectedTemplate = type;
    closeModal();
    document.getElementById('modalConfirm').style.display='flex';
}

function closeConfirm(){
    document.getElementById('modalConfirm').style.display='none';
}

function applyTemplate(){
    fetch('../api/template_produk.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'template='+selectedTemplate
    })
    .then(r=>r.json())
    .then(()=>{
        location.reload();
    });
}

function openDelete(id){
    deleteId = id;
    document.getElementById('deleteId').value = id;
    document.getElementById('modalDelete').style.display = 'flex';
}

function closeDelete(){
    document.getElementById('modalDelete').style.display = 'none';
}

function confirmDelete(){
    document.getElementById('deleteForm').submit();
}
</script>

<!-- NAV (FIX SESUAI LU) -->
<div class="bottom-nav">
<a href="kasir.php">
    <span class="nav-icon">🖩</span>
    Kasir
</a>
<a href="produk.php" class="active">
    <span class="nav-icon">📦</span>
    Produk
</a>
<a href="stok.php">
    <span class="nav-icon">📋</span>
    Stok
</a>
<a href="laporan.php">
    <span class="nav-icon">📄</span>
    Laporan
</a>
<a href="pengaturan.php">
    <span class="nav-icon">⚙️</span>
    Setelan
</a>
</div>

</body>
</html>