<?php
session_start();
// Asumsi db.php menggunakan ekstensi MySQLi
include '../config/db.php';

$error_message = null;

if(isset($_POST['submit'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // MENGGUNAKAN PREPARED STATEMENT (Mencegah SQL Injection)
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verifikasi password
    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
		session_regenerate_id(true);

        // Ambil toko menggunakan prepared statement (Best Practice)
        $stmt_toko = $conn->prepare("SELECT id FROM toko WHERE user_id = ?");
        $stmt_toko->bind_param("i", $user['id']);
        $stmt_toko->execute();
        $result_toko = $stmt_toko->get_result();
        $toko = $result_toko->fetch_assoc();
        
        if($toko) {
            $_SESSION['toko_id'] = $toko['id'];
        }
        $stmt_toko->close();

        // Redirect ke kasir
        header("Location: ../pages/kasir.php");
        exit(); // Wajib ada exit setelah header redirect
    } else {
        $error_message = "Email atau kata sandi salah!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Kasir</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Reset & Global Styles (Sama seperti Register) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #1f2937;
        }

        .login-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin: 1rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            font-size: 0.875rem;
            color: #6b7280;
        }

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

        .form-control:focus {
            border-color: #4f46e5;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #4f46e5;
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

        .error-alert {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid #f87171;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>Selamat Datang</h2>
        <p>Masuk ke akun untuk mengelola tokomu</p>
    </div>

    <?php if($error_message): ?>
        <div class="error-alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
        </div>

        <button type="submit" name="submit" class="btn-submit">Masuk</button>
    </form>

    <div class="form-footer">
        Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </div>
</div>

</body>
</html>