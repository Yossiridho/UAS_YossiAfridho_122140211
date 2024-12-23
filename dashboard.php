<?php
// Memasukkan file konfigurasi database
require 'db.php';

// Memulai session untuk memantau status pengguna
session_start();

// Mengecek apakah pengguna telah login dan memiliki peran 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // Jika tidak, arahkan pengguna ke halaman login
    header('Location: index.php');
    exit;
}

// Kelas untuk menangani pengambilan data mahasiswa dari database
class StudentFetcher {
    private $conn; // Properti untuk menyimpan koneksi database

    // Konstruktor untuk menginisialisasi koneksi database
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Metode untuk mengambil semua data mahasiswa dari tabel 'students'
    public function getAllStudents() {
        // Query untuk mengambil semua data, diurutkan berdasarkan waktu pembuatan
        $stmt = $this->conn->query("SELECT * FROM students ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengembalikan hasil dalam bentuk array asosiatif
    }
}

// Membuat instance dari StudentFetcher dan mengambil data mahasiswa
$studentFetcher = new StudentFetcher($conn);
$students = $studentFetcher->getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        /* Styling dasar untuk elemen body */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f3e7e9, #e3eeff);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
        }

        /* Styling untuk heading */
        h1, h2 {
            margin-top: 20px;
            color: #333;
        }

        /* Ukuran font untuk h1 */
        h1 {
            font-size: 2rem;
        }

        /* Ukuran font untuk h2 */
        h2 {
            font-size: 1.5rem;
        }

        /* Styling untuk tabel */
        table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
            width: 90%;
            max-width: 800px;
            margin: 20px 0;
        }

        /* Styling untuk header dan sel tabel */
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        /* Styling untuk header tabel */
        th {
            background-color: #007bff;
            color: white;
        }

        /* Warna latar belakang untuk baris genap */
        tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* Warna latar belakang saat baris di-hover */
        tr:hover {
            background: #f1f1f1;
        }

        /* Warna teks untuk sel tabel */
        tr td {
            color: #555;
        }

        /* Styling tombol logout */
        .logout {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #dc3545;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
            margin: 20px 0;
        }

        /* Warna tombol logout saat di-hover */
        .logout:hover {
            background-color: #bd2130;
        }

        /* Styling tautan di dalam tombol logout */
        .logout a {
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Menampilkan nama pengguna yang sedang login -->
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <h2>Data Mahasiswa</h2>

    <!-- Tabel untuk menampilkan data mahasiswa -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Prodi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Jika ada data mahasiswa, tampilkan -->
            <?php if (count($students) > 0): ?>
                <?php foreach ($students as $index => $student): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($student['nim']); ?></td>
                    <td><?php echo htmlspecialchars($student['nama']); ?></td>
                    <td><?php echo htmlspecialchars($student['prodi']); ?></td>
                </tr>
                <?php endforeach; ?>
            <!-- Jika tidak ada data, tampilkan pesan -->
            <?php else: ?>
            <tr>
                <td colspan="4">Belum ada data mahasiswa.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tombol untuk logout -->
    <button class="logout"><a href="logout.php">Logout</a></button>
</body>
</html>
