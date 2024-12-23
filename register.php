<?php
require 'db.php';

$error = null; // Inisialisasi variabel $error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi jika checkbox tidak dicentang
    if (!isset($_POST['agreement'])) {
        $error = "Anda harus menyetujui syarat dan ketentuan untuk melanjutkan.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords tidak sesuai.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            header('Location: index.php?register_success=true');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username sudah tersedia.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            flex-direction: column;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            background: url("https://cdn.pixabay.com/photo/2015/07/09/22/45/tree-838667_960_720.jpg") no-repeat;
            background-position: center;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .register-container {
            position: relative;
            background: transparent;
            padding: 2rem;
            border: 2px solid white;
            border-radius: 10px;
            backdrop-filter: blur(10px);
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

        p {
            color: lightcoral;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .inputbox {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .inputbox input {
            width: 100%;
            padding: 0.8rem 0 0.2rem 0.5rem;
            font-size: 1em;
            border: none;
            outline: none;
            border-bottom: 2px solid #fff;
            background: transparent;
            color: #fff;
        }

        .inputbox label {
            position: absolute;
            top: 50%;
            left: 0.5rem;
            transform: translateY(-50%);
            font-size: 1em;
            color: #fff;
            pointer-events: none;
            transition: 0.3s ease-in-out;
        }


        .inputbox input.filled ~ .floating-label,
        .inputbox input:focus ~ .floating-label {
            top: -10px;
            font-size: 0.85em;
            color: #fff;
        }

        input[type="checkbox"] {
            margin-right: 0.5rem;
            transform: scale(1.2);
            cursor: pointer;
        }

        label[for="agreement"] a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }

        label[for="agreement"] a:hover {
            color: #fff;
        }

        button {
            width: 100%;
            margin: 20px 10px;
            padding: 0.8rem;
            font-size: 1rem;
            font-weight: bold;
            color: black;
            background: #fff;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #ccc;
        }

        a {
            margin-top: 1rem;
            color: #fff;
            transition: color 0.3s;
        }

        a:hover {
            text-decoration: none;
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
            color: white;
        }

    </style>
</head>
<body>
    <div class="register-container">
        <h1>Register</h1>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
        <form method="POST" action="">
            <div class="inputbox">
                <input type="text" id="username" name="username" required>
                <label for="username" class="floating-label">Username</label>
            </div>

            <div class="inputbox">
                <input type="password" id="password" name="password" required>
                <label for="password" class="floating-label">Password</label>
            </div>
                
            <div class="inputbox">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <label for="confirm_password" class="floating-label">Confirm Password</label>
            </div>
            
            <div>
                <input type="checkbox" id="agreement" name="agreement">
                <label for="agreement">
                    Saya menyetujui 
                </label>
                <a href="#">syarat dan ketentuan</a>
            </div>

            <button type="submit">Register</button>
        </form>
        <a href="index.php">Kembali ke Login</a>
    </div>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Yossi Afridho. UAS Pemrograman Web.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('.inputbox input');

            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    if (input.value.trim() !== "") {
                        input.classList.add('filled');
                    } else {
                        input.classList.remove('filled');
                    }
                });

                if (input.value.trim() !== "") {
                    input.classList.add('filled');
                }
            });
        });
    </script>
</body>
</html>
