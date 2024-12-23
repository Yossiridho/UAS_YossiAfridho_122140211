<?php
// Memulai sesi untuk melacak login pengguna
session_start();
require 'db.php'; // Memuat file koneksi database

// Mengecek apakah pengguna sudah login sebagai admin, jika tidak maka diarahkan ke halaman index.php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Class untuk mengelola data mahasiswa
class StudentManager {
    private $conn;

    // Konstruktor menerima parameter koneksi database
    public function __construct($db) {
        $this->conn = $db;
    }

    // Mengecek apakah NIM atau Nama mahasiswa sudah ada di database
    public function isDuplicate($nim, $nama, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM students WHERE (nim = :nim OR nama = :nama)";
        if ($excludeId !== null) {
            $query .= " AND id != :id"; // Mengecualikan ID tertentu (untuk proses edit)
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nim', $nim, PDO::PARAM_STR);
        $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0; // Mengembalikan true jika duplikasi ditemukan
    }

    // Menambahkan data mahasiswa baru
    public function addStudent($nim, $nama, $prodi, $browser, $ip_address) {
        $stmt = $this->conn->prepare("INSERT INTO students (nim, nama, prodi, browser, ip_address) VALUES (:nim, :nama, :prodi, :browser, :ip)");
        $stmt->bindParam(':nim', $nim);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':prodi', $prodi);
        $stmt->bindParam(':browser', $browser);
        $stmt->bindParam(':ip', $ip_address);
        return $stmt->execute(); // Mengembalikan true jika berhasil
    }

    // Mengambil semua data mahasiswa
    public function getAllStudents() {
        $stmt = $this->conn->query("SELECT * FROM students");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengembalikan hasil dalam bentuk array asosiatif
    }

    // Menghapus data mahasiswa berdasarkan ID
    public function deleteStudent($id) {
        $stmt = $this->conn->prepare("DELETE FROM students WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute(); // Mengembalikan true jika berhasil
    }

    // Memperbarui data mahasiswa
    public function updateStudent($id, $nim, $nama, $prodi) {
        $stmt = $this->conn->prepare("UPDATE students SET nim = :nim, nama = :nama, prodi = :prodi WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nim', $nim);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':prodi', $prodi);
        return $stmt->execute(); // Mengembalikan true jika berhasil
    }
}

// Membuat objek StudentManager dengan koneksi database
$studentManager = new StudentManager($conn);

// Variabel untuk pesan SweetAlert
$alertMessage = null;
$alertType = null;

// Menangani request POST untuk menambah atau mengedit data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) { // Logika untuk menambah data
        $nim = htmlspecialchars($_POST['nim']); // Pastikan input aman
        $nama = htmlspecialchars($_POST['nama']);
        $prodi = htmlspecialchars($_POST['prodi']);
        $browser = $_SERVER['HTTP_USER_AGENT']; // Browser pengguna
        $ip_address = $_SERVER['REMOTE_ADDR']; // Alamat IP pengguna

        if (!empty($nim) && !empty($nama) && !empty($prodi)) {
            if ($studentManager->isDuplicate($nim, $nama)) {
                $alertMessage = "Gagal menambahkan data. NIM atau Nama sudah digunakan!";
                $alertType = "error";
            } else {
                if ($studentManager->addStudent($nim, $nama, $prodi, $browser, $ip_address)) {
                    $alertMessage = "Data berhasil ditambahkan!";
                    $alertType = "success";
                } else {
                    $alertMessage = "Gagal menambahkan data.";
                    $alertType = "error";
                }
            }
        } else {
            $alertMessage = "Semua kolom harus diisi.";
            $alertType = "warning";
        }
    } elseif (isset($_POST['edit_student'])) { // Logika untuk mengedit data
        $id = $_POST['student_id'];
        $nim = htmlspecialchars($_POST['nim']);
        $nama = htmlspecialchars($_POST['nama']);
        $prodi = htmlspecialchars($_POST['prodi']);

        if (!empty($nim) && !empty($nama) && !empty($prodi)) {
            if ($studentManager->isDuplicate($nim, $nama, $id)) {
                $alertMessage = "Gagal memperbarui data. NIM atau Nama sudah digunakan!";
                $alertType = "error";
            } else {
                if ($studentManager->updateStudent($id, $nim, $nama, $prodi)) {
                    $alertMessage = "Data berhasil diperbarui!";
                    $alertType = "success";
                } else {
                    $alertMessage = "Gagal memperbarui data.";
                    $alertType = "error";
                }
            }
        } else {
            $alertMessage = "Semua kolom harus diisi.";
            $alertType = "warning";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) { // Logika untuk menghapus data
    $id = $_GET['delete_id'];
    if ($studentManager->deleteStudent($id)) {
        $alertMessage = "Data berhasil dihapus!";
        $alertType = "success";
    } else {
        $alertMessage = "Gagal menghapus data.";
        $alertType = "error";
    }
}

// Mengambil semua data mahasiswa untuk ditampilkan
$students = $studentManager->getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // SweetAlert logic
        document.addEventListener("DOMContentLoaded", () => {
            const alertMessage = "<?php echo $alertMessage; ?>";
            const alertType = "<?php echo $alertType; ?>";

            if (alertMessage) {
                Swal.fire({
                    icon: alertType,
                    title: alertMessage,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        function openEditModal(id, nim, nama, prodi) {
            document.getElementById('edit_student_id').value = id;
            document.getElementById('edit_nim').value = nim;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_prodi').value = prodi;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e3eeff, #f3e7e9);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            backdrop-filter: blur(5px); /* Menambahkan efek blur pada latar belakang */
        }

        h1 {
            margin-top: 20px;
            font-size: 2rem;
            color: #333;
        }

        form, table {
            background: rgba(255, 255, 255, 0.9); /* Efek transparan putih */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px 0;
            width: 90%;
            max-width: 800px;
            backdrop-filter: blur(10px); /* Efek blur pada form dan tabel */
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        form label {
            font-size: 0.9rem;
            font-weight: bold;
            color: #555;
            width: 100%;
        }

        form input, form button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            flex: 1;
        }

        form input:focus {
            border-color: #007bff;
            outline: none;
        }

        form button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
            flex: none;
            width: 100px;
        }

        form button:hover {
            background: #0056b3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            text-align: left;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        table th {
            background: #007bff;
            color: white;
            text-align: center;
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

        table tr:hover {
            background: #f1f1f1;
        }

        table td:last-child {
            text-align: center;
        }

        table button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        table td:nth-child(7) {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        table button.edit {
            background: #ffc107;
            color: white;
        }

        table button.edit:hover {
            background: #e0a800;
        }

        table button.delete {
            background: #dc3545;
            color: white;
        }

        table button.delete:hover {
            background: #bd2130;
        }

        table button.delete a {
            color: white;
            text-decoration: none;
            display: block;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            backdrop-filter: blur(5px); /* Blur pada modal */
        }

        .modal-content input {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .modal-content button.save {
            background: #28a745;
            color: white;
        }

        .modal-content button.save:hover {
            background: #218838;
        }

        .modal-content button.cancel {
            background: #6c757d;
            color: white;
        }

        .modal-content button.cancel:hover {
            background: #5a6268;
        }

        button.logout {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #dc3545;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin: 20px 0;
        }

        button.logout:hover {
            background-color: #bd2130;
        }

        button.logout a {
            color: white;
            text-decoration: none;
            display: block;
        }

    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <!-- SweetAlert success/error notifications -->
    <form method="POST" action="">
        <label for="nim">NIM:</label>
        <input type="text" name="nim" required>
        <label for="nama">Nama:</label>
        <input type="text" name="nama" required>
        <label for="prodi">Prodi:</label>
        <input type="text" name="prodi" required>
        <button type="submit" name="add_student">Tambah</button>
    </form>

    <table>
        <tr>
            <th>No</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Prodi</th>
            <th>Browser</th>
            <th>IP Address</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($students as $index => $student): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars($student['nim']); ?></td>
                <td><?php echo htmlspecialchars($student['nama']); ?></td>
                <td><?php echo htmlspecialchars($student['prodi']); ?></td>
                <td><?php echo htmlspecialchars($student['browser']); ?></td>
                <td><?php echo htmlspecialchars($student['ip_address']); ?></td>
                <td>
                    <button class="edit" onclick="openEditModal(<?php echo $student['id']; ?>, '<?php echo $student['nim']; ?>', '<?php echo $student['nama']; ?>', '<?php echo $student['prodi']; ?>')">Edit</button>
                    <button class="delete"><a href="?delete_id=<?php echo $student['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</a></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Modal untuk Edit Data Mahasiswa -->
    <div id="editModal" style="display: none;">
        <form method="POST">
            <input type="hidden" id="edit_student_id" name="student_id">
            <label for="edit_nim">NIM:</label>
            <input type="text" id="edit_nim" name="nim" required>
            <label for="edit_nama">Nama:</label>
            <input type="text" id="edit_nama" name="nama" required>
            <label for="edit_prodi">Prodi:</label>
            <input type="text" id="edit_prodi" name="prodi" required>
            <button type="submit" name="edit_student">Simpan</button>
            <button type="button" onclick="closeEditModal()">Batal</button>
        </form>
    </div>
    <!-- Tombol untuk logout -->
    <button class="logout"><a href="logout.php">Logout</a></button>
</body>
</html>
