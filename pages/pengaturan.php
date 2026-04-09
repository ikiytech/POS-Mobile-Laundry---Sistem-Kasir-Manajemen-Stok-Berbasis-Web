<?php
session_start();
include '../config/db.php';

if(!isset($_SESSION['toko_id'])) {
    header("Location: ../login.php");
    exit();
}

$toko_id = $_SESSION['toko_id'];
$pesan = "";

// ========================
// UPDATE DATA TOKO (UPGRADE)
// ========================
if(isset($_POST['update_toko'])){
    $nama_toko = trim($_POST['nama_toko']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $catatan = trim($_POST['catatan']);
    
    $stmt = $conn->prepare("
        UPDATE toko 
        SET nama_toko = ?, alamat = ?, telepon = ?, catatan = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $nama_toko, $alamat, $telepon, $catatan, $toko_id);
    
    if($stmt->execute()){
        $pesan = "<div class='alert success'>Pengaturan berhasil disimpan!</div>";
    }
    $stmt->close();
}

// ========================
// AMBIL DATA TOKO (UPGRADE)
// ========================
$stmt = $conn->prepare("SELECT * FROM toko WHERE id = ?");
$stmt->bind_param("i", $toko_id);
$stmt->execute();
$toko = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Kasir POS</title>
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
            --border-color: #e5e7eb; 
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); padding-bottom: 80px; }
        
        .header { 
            display: flex; justify-content: center; align-items: center; 
            padding: 1.2rem 1rem; background: var(--card-bg); position: sticky; 
            top: 0; z-index: 20; box-shadow: 0 1px 3px rgba(0,0,0,0.02); 
        }
        .header h2 { font-size: 1.1rem; font-weight: 700; }
        
        .container { padding: 1rem; max-width: 600px; margin: 0 auto; }
        
        .card { 
            background: var(--card-bg); border-radius: 14px; padding: 1.5rem; 
            border: 1px solid var(--border-color); margin-bottom: 1.5rem; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); 
        }
        .card-title { font-size: 1rem; font-weight: 700; margin-bottom: 1.2rem; color: var(--text-main); }
        
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { 
            display: block; font-size: 0.85rem; font-weight: 600; 
            color: var(--text-muted); margin-bottom: 0.5rem; 
        }
        .form-control { 
            width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); 
            border-radius: 10px; font-size: 0.95rem; outline: none; 
            background: var(--bg-color); transition: border 0.2s; 
        }
        .form-control:focus { border-color: var(--primary); background: var(--card-bg); }

        textarea.form-control {
            min-height: 70px;
            resize: none;
        }
        
        .btn-primary { 
            background: var(--primary); color: white; border: none; 
            padding: 1rem; border-radius: 12px; width: 100%; 
            font-size: 1rem; font-weight: 700; cursor: pointer; 
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: transform 0.2s; 
            margin-top: 0.5rem;
        }
        .btn-primary:active { transform: scale(0.98); }
        
        .btn-logout { 
            background: #fee2e2; color: #b91c1c; border: none; padding: 1rem; 
            border-radius: 12px; width: 100%; font-size: 1rem; font-weight: 700; 
            cursor: pointer; text-align: center; text-decoration: none; 
            display: block; transition: background 0.2s;
        }
        .btn-logout:hover { background: #fca5a5; }
        
        .alert { 
            padding: 0.8rem; border-radius: 10px; margin-bottom: 1.2rem; 
            font-size: 0.9rem; text-align: center; font-weight: 500; 
        }
        .alert.success { background: var(--primary-light); color: var(--primary-dark); border: 1px solid #a7f3d0; }

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
    <h2>Pengaturan Toko</h2>
</div>

<div class="container">
    <?= $pesan ?>

    <div class="card">
        <h3 class="card-title">Profil Toko</h3>

        <form method="POST">

            <div class="form-group">
                <label>Nama Toko</label>
                <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($toko['nama_toko']) ?>" required>
            </div>

            <div class="form-group">
                <label>Alamat Lengkap</label>
                <textarea name="alamat" class="form-control"><?= htmlspecialchars($toko['alamat'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($toko['telepon'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Catatan Kaki Struk</label>
                <textarea name="catatan" class="form-control"><?= htmlspecialchars($toko['catatan'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="update_toko" class="btn-primary">Simpan Pengaturan</button>

        </form>
    </div>

    <a href="../logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar dari aplikasi?')">
        Keluar Akun (Logout)
    </a>
</div>

<div class="bottom-nav">
    <a href="kasir.php"><span class="nav-icon">🖩</span>Kasir</a>
    <a href="produk.php"><span class="nav-icon">📦</span>Produk</a>
    <a href="stok.php"><span class="nav-icon">📋</span>Stok</a>
    <a href="laporan.php"><span class="nav-icon">📄</span>Laporan</a>
    <a href="pengaturan.php" class="active"><span class="nav-icon">⚙️</span>Setelan</a>
</div>

</body>
</html>