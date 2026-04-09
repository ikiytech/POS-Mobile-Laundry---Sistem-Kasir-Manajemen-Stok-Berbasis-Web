<?php
session_start();
include '../config/db.php';

// ========================
// EXPORT CSV
// ========================
if(isset($_GET['export'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['Kode', 'Total', 'Metode', 'Tanggal']);

    $data = $conn->query("SELECT * FROM transaksi ORDER BY created_at DESC");

    while($row = $data->fetch_assoc()){
        fputcsv($output, [$row['kode'], $row['total'], $row['metode'], $row['created_at']]);
    }
    fclose($output);
    exit();
}

// ========================
// DATA
// ========================
$hari_ini = date('Y-m-d');

$stmt = $conn->prepare("SELECT SUM(total) as omzet, COUNT(id) as total_trx FROM transaksi WHERE DATE(created_at)=?");
$stmt->bind_param("s",$hari_ini);
$stmt->execute();
$h = $stmt->get_result()->fetch_assoc();

$omzet = $h['omzet'] ?? 0;
$total_trx = $h['total_trx'] ?? 0;

$riwayat = $conn->query("SELECT * FROM transaksi ORDER BY id DESC LIMIT 20");

// CHART
$chart = [];
$q = $conn->query("
    SELECT DATE(created_at) as tanggal, SUM(total) as total 
    FROM transaksi 
    GROUP BY DATE(created_at)
");
while($r = $q->fetch_assoc()){
    $chart[] = $r;
}

// INSIGHT
$top = $conn->query("
    SELECT p.nama, SUM(td.qty) as total 
    FROM transaksi_detail td
    JOIN produk p ON p.id = td.produk_id
    GROUP BY p.id
    ORDER BY total DESC
    LIMIT 1
");

$top_produk = $top->fetch_assoc();
$insight = $top_produk 
    ? "Produk terlaris: {$top_produk['nama']} ({$top_produk['total']}x)"
    : "Belum ada data";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan</title>

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

* {margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body {background:var(--bg-color);color:var(--text-main);padding-bottom:100px;}

.header{
    padding:1.2rem 1rem 0.5rem 1rem;
    background:var(--card-bg);
    text-align:center;
    font-weight:700;
}

.container{
    padding:1rem;
    max-width:600px;
    margin:auto;
}

.summary-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:1rem;
    margin-bottom:1.5rem;
}

.summary-card{
    background:var(--card-bg);
    padding:1rem;
    border-radius:14px;
    text-align:center;
    border:1px solid var(--border-color);
}

.summary-card p{
    font-size:0.75rem;
    color:var(--text-muted);
    margin-bottom:5px;
}

.summary-card h3{
    color:var(--primary);
}

.card{
    background:var(--card-bg);
    border-radius:14px;
    padding:1rem;
    margin-bottom:1rem;
}

.section-title{
    font-weight:700;
    margin-bottom:10px;
}

.list-item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid var(--border-color);
}

.list-item:last-child{border:none;}

.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:12px;
    background:var(--primary);
    color:white;
    margin-bottom:1rem;
}

/* 🔥 NAV SAMA PERSIS */
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
    z-index: 20;
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
</style>
</head>

<body>

<div class="header"><h2>Laporan</h2></div>

<div class="container">

    <div class="summary-grid">
        <div class="summary-card">
            <p>Pendapatan Hari Ini</p>
            <h3>Rp <?= number_format($omzet,0,',','.') ?></h3>
        </div>
        <div class="summary-card">
            <p>Total Transaksi</p>
            <h3><?= $total_trx ?></h3>
        </div>
    </div>

    <div class="card">
        <b>Insight</b><br>
        <?= $insight ?>
    </div>

    <a href="?export=1">
        <button class="btn">Export Excel</button>
    </a>

    <div class="section-title">Grafik</div>
    <div class="card">
        <canvas id="chart"></canvas>
    </div>

    <div class="section-title">Riwayat</div>
    <div class="card">
        <?php while($t=$riwayat->fetch_assoc()): ?>
        <div class="list-item">
            <div>
                <b><?= $t['kode'] ?></b><br>
                <small><?= $t['created_at'] ?></small>
            </div>
            <div>Rp <?= number_format($t['total'],0,',','.') ?></div>
        </div>
        <?php endwhile; ?>
    </div>

</div>

<!-- NAV FIX -->
<div class="bottom-nav">
    <a href="kasir.php"><span class="nav-icon">🖩</span>Kasir</a>
    <a href="produk.php"><span class="nav-icon">📦</span>Produk</a>
    <a href="stok.php"><span class="nav-icon">📋</span>Stok</a>
    <a href="laporan.php" class="active"><span class="nav-icon">📄</span>Laporan</a>
    <a href="pengaturan.php"><span class="nav-icon">⚙️</span>Setelan</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const d = <?= json_encode($chart); ?>;

new Chart(document.getElementById('chart'), {
    type:'line',
    data:{
        labels:d.map(x=>x.tanggal),
        datasets:[{data:d.map(x=>x.total),tension:0.4}]
    },
    options:{plugins:{legend:{display:false}}}
});
</script>

</body>
</html>