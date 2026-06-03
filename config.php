<?php
// Pengaturan Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "absensi_db";

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset ke utf8mb4 agar mendukung semua karakter
mysqli_set_charset($conn, "utf8mb4");

// Opsi tambahan: Mengatur zona waktu ke WIB (Jakarta)
date_default_timezone_set("Asia/Jakarta");
?>