<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['login'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: laporan.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

class Login {
    private $db;

    public function __construct($dbConnection){
        $this->db = $dbConnection;
    }

    public function masuk($username, $password){
        $stmt = $this->db->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['login']    = true;
                $_SESSION['user_id']  = $user['id'] ?? $user['id_user'] ?? 0;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'] ?? $user['level'] ?? 'guru';

                $_SESSION['nuptk']      = '-';
                $_SESSION['bidang']     = '-';
                $_SESSION['wali_kelas'] = '-';
                $_SESSION['ruangan']    = '-';

                $stmt->close();

                if ($_SESSION['role'] === 'admin') {
                    header("Location: laporan.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        }
        
        $stmt->close();
        return "Username atau password salah!";
    }
}

$login = new Login($conn);
$error = "";

if (isset($_POST['login'])) {
    $error = $login->masuk(trim($_POST['user']), $_POST['pass']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Sistem Kehadiran</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #eef2f7;
    padding: 20px;
}
.card {
    width: 100%;
    max-width: 750px;
    display: flex;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}
.sisi-kiri {
    flex: 1;
    background: linear-gradient(160deg, #0C447C, #378ADD);
    padding: 48px 36px;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.logo-teks {
    font-size: 18px;
    font-weight: 600;
}
.info-judul {
    font-size: 24px;
    margin-bottom: 12px;
}
.info-deskripsi {
    font-size: 13px;
    line-height: 1.7;
    color: rgba(255,255,255,0.8);
}
.versi {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
}
.sisi-kanan {
    width: 320px;
    background: white;
    padding: 48px 32px;
}
.form-judul {
    font-size: 22px;
    margin-bottom: 4px;
}
.form-subjudul {
    font-size: 13px;
    color: #999;
    margin-bottom: 24px;
}
.pesan-error {
    background: #fcebeb;
    color: #a32d2d;
    border: 1px solid #f09595;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 12px;
}
.form-grup {
    margin-bottom: 16px;
}
.form-grup label {
    font-size: 11px;
    color: #888;
    display: block;
    margin-bottom: 5px;
}
.form-grup input {
    width: 100%;
    height: 42px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0 14px;
    background: #f8f9fa;
    font-family: 'Poppins';
}
.form-grup input:focus {
    outline: none;
    border-color: #378ADD;
    background: white;
}
.tombol-login {
    width: 100%;
    height: 44px;
    border: none;
    border-radius: 8px;
    background: #185FA5;
    color: white;
    font-family: 'Poppins';
    cursor: pointer;
    margin-top: 10px;
    transition: 0.2s;
}
.tombol-login:hover {
    background: #0C447C;
}
</style>
</head>
<body>
<div class="card">
    <div class="sisi-kiri">
        <div class="logo-teks">Sistem Kehadiran</div>
        <div>
            <h2 class="info-judul">Kelola kehadiran siswa dengan mudah</h2>
            <p class="info-deskripsi">
                Platform digital untuk guru dan staf sekolah
                dalam mencatat dan memantau kehadiran siswa.
            </p>
        </div>
        <div class="versi">SMK NEGERI 1 JAKARTA</div>
    </div>
    <div class="sisi-kanan">
        <h3 class="form-judul">Selamat datang</h3>
        <p class="form-subjudul">Masuk untuk melanjutkan ke sistem</p>
        <?php if (!empty($error)): ?>
            <div class="pesan-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-grup">
                <label>Username</label>
                <input type="text" name="user" placeholder="Masukkan username" required>
            </div>
            <div class="form-grup">
                <label>Password</label>
                <input type="password" name="pass" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="tombol-login" name="login">Masuk</button>
        </form>
    </div>
</div>
</body>
</html>