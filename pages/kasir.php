<?php
session_start();
include '../config/db.php';

// FIX LOGIN GUARD (dipindah ke atas biar aman)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fungsi untuk mengambil 2 huruf awal dari nama produk
function getInitials($string) {
    $words = explode(" ", $string);
    $initials = "";
    foreach ($words as $w) {
        if (!empty($w)) $initials .= strtoupper($w[0]);
    }
    return substr($initials, 0, 2);
}

// Ambil kategori
$kategori_query = $conn->query("SELECT DISTINCT kategori FROM produk WHERE aktif = 1 AND kategori IS NOT NULL");

// Ambil produk
$produk_query = $conn->query("SELECT * FROM produk WHERE aktif = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir POS</title>
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); padding-bottom: 160px; }

        /* Header */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.2rem 1rem 0.5rem 1rem; background: var(--card-bg); 
            position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .header h2 { font-size: 1.2rem; font-weight: 700; width: 100%; text-align: center; }
        .header .reload-icon { position: absolute; right: 1.2rem; cursor: pointer; color: var(--text-main); font-size: 1.2rem; text-decoration: none; }

        /* Search */
        .search-container { padding: 0.5rem 1rem; position: sticky; top: 50px; background: var(--bg-color); z-index: 10;}
        .search-box {
            display: flex; align-items: center; background: var(--card-bg);
            border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.6rem 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .search-box input { border: none; outline: none; width: 100%; margin-left: 0.5rem; font-size: 0.95rem; }
        
        /* Kategori Scroll */
        .category-scroll {
            display: flex; overflow-x: auto; padding: 0.5rem 1rem 1rem 1rem;
            gap: 0.8rem; scrollbar-width: none; background: var(--bg-color);
        }
        .category-scroll::-webkit-scrollbar { display: none; }
        .cat-chip {
            white-space: nowrap; padding: 0.4rem 1rem; border-radius: 20px;
            font-size: 0.85rem; font-weight: 600; cursor: pointer;
            background: transparent; color: var(--text-muted); border: 1px solid transparent;
        }
        .cat-chip.active { background: var(--primary-light); color: var(--primary-dark); }

        /* Product Grid */
        .product-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 0 1rem; }
        .product-card {
            background: var(--card-bg); border-radius: 14px; padding: 0.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); display: flex; flex-direction: column; position: relative;
        }
        
        .img-placeholder {
            background: var(--primary-light); border-radius: 10px; height: 90px;
            display: flex; justify-content: center; align-items: center;
            font-size: 1.8rem; font-weight: 700; color: var(--primary-dark);
            margin-bottom: 0.8rem; position: relative;
        }
        .stock-badge {
            position: absolute; top: 6px; right: 6px; background: white;
            color: var(--text-muted); font-size: 0.7rem; font-weight: 700;
            padding: 0.2rem 0.4rem; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .prod-name { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem; line-height: 1.3; }
        .prod-cat { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.8rem; }
        
        .card-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: auto;}
        .prod-price { font-size: 0.95rem; font-weight: 700; color: var(--text-main); }
        
        .qty-control { display: flex; align-items: center; background: var(--border-color); border-radius: 20px; padding: 2px; }
        .qty-btn { 
            width: 28px; height: 28px; border-radius: 50%; border: none;
            background: transparent; color: var(--text-muted); font-weight: bold; cursor: pointer;
        }
        .qty-btn.add { background: var(--primary); color: white; }
        .qty-val { width: 24px; text-align: center; font-size: 0.85rem; font-weight: 600; border: none; background: transparent; }

        /* Floating Checkout Bar */
        .checkout-bar {
            position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
            width: calc(100% - 2rem); max-width: 560px;
            background: var(--primary); color: white;
            border-radius: 16px; padding: 1rem 1.2rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); z-index: 15;
            cursor: pointer; transition: transform 0.2s;
        }
        .checkout-bar:active { transform: translateX(-50%) scale(0.98); }
        .checkout-left { display: flex; align-items: center; gap: 0.8rem; }
        .item-count { background: white; color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 0.85rem; font-weight: 700;}
        .total-price { font-size: 1.1rem; font-weight: 700; }
        .checkout-text { font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 5px;}

        /* Bottom Nav */
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

    <div class="header">
        <h2>Kasir</h2>
        <a href="kasir.php" class="reload-icon">↻</a>
    </div>

    <div class="search-container">
        <div class="search-box">
            <span>🔍</span>
            <input type="text" id="searchInput" placeholder="Cari nama produk...">
        </div>
    </div>
	
    <div class="category-scroll">
        <div class="cat-chip active">Semua</div>
        <?php while($k = $kategori_query->fetch_assoc()): ?>
            <div class="cat-chip"><?= htmlspecialchars($k['kategori']) ?></div>
        <?php endwhile; ?>
    </div>

    <div class="product-grid">
        <?php while($p = $produk_query->fetch_assoc()): ?>
        <div class="product-card">
            <div class="img-placeholder">
                <?= getInitials($p['nama']) ?>
                <span class="stock-badge"><?= $p['stok'] ?></span>
            </div>
            
            <div class="prod-name"><?= htmlspecialchars($p['nama']) ?></div>
            <div class="prod-cat"><?= htmlspecialchars($p['kategori'] ?? 'Umum') ?></div>
            
            <div class="card-bottom">
                <div class="prod-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                <div class="qty-control">
                    <button class="qty-btn min" onclick="changeQty(<?= $p['id'] ?>, '<?= addslashes($p['nama']) ?>', <?= $p['harga'] ?>, -1)">-</button>
                    <input type="text" class="qty-val" id="qty-<?= $p['id'] ?>" value="0" readonly>
                    <button class="qty-btn add" onclick="changeQty(<?= $p['id'] ?>, '<?= addslashes($p['nama']) ?>', <?= $p['harga'] ?>, 1)">+</button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="checkout-bar" onclick="goCheckout()">
        <div class="checkout-left">
            <div class="item-count" id="cart-count">0</div>
            <div class="total-price" id="total">Rp 0</div>
        </div>
        <div class="checkout-text">
            Checkout <span>❯</span>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="kasir.php" class="active"><span class="nav-icon">🖩</span>Kasir</a>
        <a href="produk.php"><span class="nav-icon">📦</span>Produk</a>
        <a href="stok.php"><span class="nav-icon">📋</span>Stok</a>
        <a href="laporan.php"><span class="nav-icon">📄</span>Laporan</a>
        <a href="pengaturan.php"><span class="nav-icon">⚙️</span>Setelan</a>
    </div>

    <script src="../assets/app.js"></script>
</body>
</html>