<?php
// Pastikan file db.php sudah menggunakan ekstensi MySQLi atau PDO. 
// Asumsi di bawah menggunakan MySQLi.
include '../config/db.php';

if(isset($_POST['submit'])){
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // MENGGUNAKAN PREPARED STATEMENT UNTUK KEAMANAN (Mencegah SQL Injection)
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $email, $password);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;

        // Auto buat toko dengan Prepared Statement
        $nama_toko_awal = 'Toko Saya';
        $stmt_toko = $conn->prepare("INSERT INTO toko (user_id, nama_toko) VALUES (?, ?)");
        $stmt_toko->bind_param("is", $user_id, $nama_toko_awal);
        $stmt_toko->execute();
        $stmt_toko->close();

        // Redirect ke login
        header("Location: login.php");
        exit(); // Selalu tambahkan exit() setelah perintah header redirect
    } else {
        $error_message = "Terjadi kesalahan saat mendaftar.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Reset & Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6; /* Warna abu-abu terang yang lembut */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #1f2937;
        }

        /* Desain Card Utama */
        .register-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin: 1rem;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Styling Form & Input */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            transition: all 0.3s ease;
            background-color: #f9fafb;
        }

        /* UX: Efek fokus pada input */
        .form-control:focus {
            border-color: #4f46e5;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        /* Styling Tombol */
        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #4f46e5; /* Biru keunguan modern */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            background-color: #4338ca;
        }

        .btn-submit:active {
            transform: translateY(2px);
        }

        /* Bagian Link Bawah */
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .form-footer a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: #3730a3;
            text-decoration: underline;
        }

        /* Pesan Error (Jika ada) */
        .error-alert {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #f87171;
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-header">
        <h2>Buat Akun</h2>
        <p>Daftar untuk mulai mengelola tokomu</p>
    </div>

    <?php if(isset($error_message)): ?>
        <div class="error-alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama kamu" required>
        </div>

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
        </div>

        <button type="submit" name="submit" class="btn-submit">Daftar Sekarang</button>
    </form>

    <div class="form-footer">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
    </div>
</div>

</body>
</html>