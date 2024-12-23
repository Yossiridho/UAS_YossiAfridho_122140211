<?php
require 'db.php'; // Memuat file koneksi database
session_start(); // Memulai sesi PHP

// Mengecek apakah pengguna datang dari halaman registrasi dengan parameter 'register_success'
if (isset($_GET['register_success']) && $_GET['register_success'] == 'true') {
    $success_message = "Registrasi berhasil! Silakan login."; // Menampilkan pesan sukses registrasi
}

$error = null; // Inisialisasi variabel $error untuk menampung pesan error

// Mengecek apakah metode request adalah POST (pengguna mencoba login)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Menghapus spasi tambahan pada username
    $password = trim($_POST['password']); // Menghapus spasi tambahan pada password

    if (!empty($username) && !empty($password)) { // Validasi input tidak kosong
        // Menyiapkan query untuk mencari username dalam database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username); // Mengikat parameter username
        $stmt->execute(); // Menjalankan query
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Mendapatkan hasil sebagai array asosiatif

        if ($user) { // Jika pengguna ditemukan
            // Verifikasi password menggunakan fungsi password_verify
            if (password_verify($password, $user['password'])) {
                // Menyimpan data pengguna ke sesi
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Menyimpan username ke dalam cookie (berlaku 30 hari)
                setcookie('username', $user['username'], time() + (86400 * 30), "/");

                // Menyimpan informasi ke localStorage menggunakan JavaScript
                echo "<script>localStorage.setItem('username', '" . $user['username'] . "');</script>";

                // Mengarahkan pengguna ke dashboard berdasarkan perannya
                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php'); // Redirect ke dashboard admin
                } else {
                    header('Location: dashboard.php'); // Redirect ke dashboard user
                }
                exit; // Menghentikan eksekusi script
            } else {
                $error = "Password salah. Silakan coba lagi."; // Password tidak cocok
            }
        } else {
            $error = "Username tidak tersedia. Silakan register terlebih dahulu."; // Username tidak ditemukan
        }
    } else {
        $error = "Username dan password harus diisi."; // Input kosong
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script>
        // Mengecek localStorage saat halaman dimuat
        window.onload = function() {
            let username = localStorage.getItem('username'); // Mendapatkan username dari localStorage
            let role = localStorage.getItem('role'); // Mendapatkan role dari localStorage

            if (username && role) { // Jika username dan role tersedia
                if (role === 'admin') {
                    window.location.href = 'admin_dashboard.php'; // Redirect ke dashboard admin
                } else {
                    window.location.href = 'dashboard.php'; // Redirect ke dashboard user
                }
            }
        };
    </script>
    <style>
        /* Gaya tampilan halaman login */
        body {
            color: #fff;
            font-family: 'Poppins', sans-serif;
            background: url("https://cdn.pixabay.com/photo/2015/07/09/22/45/tree-838667_960_720.jpg") no-repeat;
            background-position: center;
            background-size: cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: transparent;
            border: 2px solid white;
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .error-message {
            color: lightcoral;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .inputbox {
            position: relative;
            border-bottom: 2px solid #fff;
            margin-bottom: 5px;
        }

        .inputbox label {
            position: absolute;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1em;
            pointer-events: none;
            transition: 0.5s;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            border: none;
            outline: none;
            background: transparent;
            color: #fff;
        }

        input:focus ~ label,
        input:valid ~ label {
            top: -5px;
            font-size: 0.9em;
        }

        button {
            margin: 10px 20px;
            padding: 0.8rem;
            font-size: 1rem;
            font-weight: bold;
            color: black;
            background: #fff;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        button:hover {
            background: #ccc;
        }

        a {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #fff;
            transition: color 0.3s;
        }

        a:hover {
            text-decoration: none;
        }

        .success-message {
            color: lightgreen;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: grey;
            padding: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            height: 6%;
        }

        footer p {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>

        <!-- Menampilkan pesan sukses jika ada -->
        <?php if (!$error && isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Menampilkan pesan error jika ada -->
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form login -->
        <form method="POST" action="">
            <div class="inputbox">
                <input type="text" id="username" name="username" required>
                <label for="username">Username</label>
            </div>

            <div class="inputbox">
                <input type="password" id="password" name="password" required>
                <label for="password">Password</label>
            </div>

            <button type="submit">Login</button>
        </form>

        <!-- Link ke halaman registrasi -->
        Belum memiliki akun? <a href="register.php">Registrasi disini</a>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Yossi Afridho. UAS Pemrograman Web.</p>
    </footer>
</body>
</html>
