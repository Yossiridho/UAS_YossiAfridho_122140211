<?php
// Konfigurasi koneksi ke database
$host = 'localhost';       // Host database, biasanya 'localhost' untuk server lokal
$dbname = 'user_auth';     // Nama database yang akan digunakan
$username = 'root';        // Nama pengguna database
$password = '';            // Kata sandi pengguna database (kosong jika menggunakan server lokal default)

try {
    // Membuat koneksi ke database menggunakan PDO (PHP Data Objects)
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Mengatur mode error untuk PDO menjadi Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Menangkap error koneksi database dan menghentikan eksekusi skrip dengan pesan error
    die("Database connection failed: " . $e->getMessage());
}
?>

