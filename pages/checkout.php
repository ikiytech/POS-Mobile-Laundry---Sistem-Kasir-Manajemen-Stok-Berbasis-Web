<?php session_start(); if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); } ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kasir POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #10b981; --primary-light: #d1fae5; --bg-color: #f9fafb; --card-bg: #ffffff; --text-main: #1f2937; --text-muted: #6b7280; --border-color: #f3f4f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); }
        .header { display: flex; align-items: center; padding: 1.2rem 1rem; background: var(--card-bg); position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .header a { text-decoration: none; font-size: 1.2rem; color: var(--text-main); margin-right: 1rem; }
        .header h2 { font-size: 1.1rem; font-weight: 700; }
        .container { padding: 1rem; max-width: 600px; margin: 0 auto; }
        .card { background: var(--card-bg); border-radius: 14px; padding: 1.2rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card-title { font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); }
        #cart-list { min-height: 50px; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;}
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 1rem; outline: none; background: var(--bg-color); }
        .form-control:focus { border-color: var(--primary); background: var(--card-bg); }
        .btn-pay { background: var(--primary); color: white; border: none; padding: 1rem; border-radius: 12px; width: 100%; font-size: 1.1rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); margin-top: 1rem; }
        
        /* --- STYLE KHUSUS MODAL POP-UP --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); display: none; /* Awalnya disembunyikan */
            justify-content: center; align-items: center; z-index: 100;
            backdrop-filter: blur(3px);
        }
        .modal-card {
            background: var(--card-bg); padding: 2rem 1.5rem; border-radius: 16px;
            width: 90%; max-width: 350px; text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-icon { font-size: 3.5rem; margin-bottom: 0.5rem; }
        .modal-title { font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; }
        .modal-text { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.4; }
        
        .modal-buttons { display: flex; gap: 0.8rem; justify-content: center; }
        .btn-outline { background: transparent; border: 1px solid #d1d5db; color: var(--text-main); padding: 0.8rem; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn-fill { background: var(--primary); border: none; color: white; padding: 0.8rem; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <div class="header"><a href="kasir.php">←</a><h2>Checkout Pesanan</h2></div>
    
    <div class="container">
        <div class="card">
            <div class="card-title">Ringkasan Pesanan</div>
            <div id="cart-list"><em>Memuat pesanan...</em></div>
        </div>
        <div class="card">
            <div class="card-title">Pembayaran</div>
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select id="metode" class="form-control">
                    <option value="CASH">Tunai</option>
                    <option value="QRIS">QRIS</option>
                    <option value="TRANSFER">Transfer</option>
                </select>
            </div>
            <div class="form-group">
                <label>Uang Diterima (Rp)</label>
                <input type="number" id="bayar" class="form-control" placeholder="Masukkan nominal uang">
            </div>
            <button onclick="prosesBayar()" class="btn-pay">Proses Pembayaran ✓</button>
        </div>
    </div>

    <div id="errorModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-icon">⚠️</div>
            <div class="modal-title">Perhatian</div>
            <div class="modal-text" id="errorMessage">Pesan error di sini.</div>
            <button class="btn-outline" onclick="closeModal('errorModal')">Mengerti</button>
        </div>
    </div>

    <div id="successModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-icon" style="color: var(--primary);">✅</div>
            <div class="modal-title">Pembayaran Sukses!</div>
            <div class="modal-text" id="successMessage">Kembalian: Rp 0</div>
            <div class="modal-buttons">
                <button class="btn-outline" onclick="selesaiTransaksi()">Selesai</button>
                <button class="btn-fill" onclick="cetakStruk()">🖨️ Cetak Struk</button>
            </div>
        </div>
    </div>

    <script src="../assets/app.js"></script>
</body>
</html>