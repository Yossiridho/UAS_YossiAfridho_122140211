<?php
session_start();
session_destroy();

// Hapus semua cookie
foreach ($_COOKIE as $key => $value) {
    setcookie($key, '', time() - 3600, "/");
}

header('Location: index.php');
exit;
?>
