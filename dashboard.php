<?php
include 'config.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? $_SESSION['level'] ?? '';

if ($role !== 'guru' && $role !== 'admin') {
    die("Akses ditolak! Halaman ini khusus untuk Guru.");
}

$id_guru = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'Guru';

// Ambil data detail guru yang sedang login secara realtime dari database
$query_guru = $conn->query("SELECT * FROM user WHERE id = '$id_guru'");
$data_guru = $query_guru->fetch_assoc();

// Hitung total kelas berdasarkan string yang dipisah koma di database
$total_kelas = 0;
if (!empty($data_guru['kelas_diajar'])) {
    $arr_kelas = explode(", ", $data_guru['kelas_diajar']);
    $total_kelas = count($arr_kelas);
}

class InfoSekolah {
    public $namaSekolah;
    public $tahunPelajaran;
    public $semester;

    public function __construct($nama, $tp, $semester) {
        $this->namaSekolah = $nama;
        $this->tahunPelajaran = $tp;
        $this->semester = $semester;
    }
}

$infoSekolah = new InfoSekolah(
    "SMK NEGERI 1 JAKARTA",
    "TP. 2025-2026",
    "AKHIR SEMESTER GENAP"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kejar.Prestasi - Dashboard Guru</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --primary-color: #4338ca;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); padding-bottom: 40px; }

        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            background-color: var(--card-bg); padding: 14px 5%;
            border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 200;
        }
        .brand { display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--success); font-size: 1.2rem; }
        
        .user-profile-nav {
            position: relative;
            display: flex; align-items: center; gap: 10px;
            font-size: 0.9rem; font-weight: 600;
            cursor: pointer; user-select: none;
        }
        .avatar-nav {
            width: 32px; height: 32px; border-radius: 50%;
            background-color: var(--warning); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 0.9rem;
        }
        .chevron-icon {
            font-size: 0.75rem; color: var(--text-muted);
            transition: transform 0.25s ease;
        }
        .user-profile-nav.open .chevron-icon {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            display: none;
            position: absolute; top: calc(100% + 12px); right: 0;
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            min-width: 230px;
            overflow: hidden;
            z-index: 300;
            animation: dropIn 0.2s ease;
        }
        .user-profile-nav.open .dropdown-menu {
            display: block;
        }
        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            display: flex; align-items: center; gap: 13px;
            padding: 13px 18px;
            font-size: 0.88rem; font-weight: 500; color: var(--text-main);
            text-decoration: none; cursor: pointer;
            transition: background 0.15s;
        }
        .dropdown-item:hover { background-color: #f1f5f9; }
        .dropdown-item i {
            width: 20px; text-align: center;
            color: var(--text-muted); font-size: 1rem;
        }
        .dropdown-item.logout {
            border-top: 1px solid var(--border-color);
            color: #ef4444;
        }
        .dropdown-item.logout i { color: #ef4444; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 20px; }

        .banner-card {
            background-color: #eef2ff;
            border-radius: 20px; padding: 36px 20px 28px;
            text-align: center;
            display: flex; flex-direction: column; align-items: center;
        }
        .logo-sekolah {
            width: 60px; height: 60px; background-color: #0b4084;
            border-radius: 50%; margin-bottom: 15px;
            display: flex; align-items: center; justify-content: center;
            color: #fffb00; font-size: 1.6rem;
        }
        .judul-sekolah { font-size: 2.2rem; font-weight: 800; color: #1e3a8a; margin-bottom: 8px; }
        .sub-banner {
            font-size: 0.9rem; color: #64748b; font-weight: 500;
            margin-bottom: 18px; text-transform: uppercase; letter-spacing: 0.5px;
        }

        .info-detail-guru {
            background: white; border: 1px solid var(--border-color); width: 100%; max-width: 600px;
            padding: 15px 20px; border-radius: 12px; margin-top: 10px; text-align: left;
        }
        .detail-row { display: flex; margin-bottom: 8px; font-size: 0.9rem; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { width: 140px; font-weight: 600; color: var(--text-muted); }
        .detail-value { flex: 1; color: var(--text-main); }
        .badge-info-dashboard { display: inline-block; padding: 2px 8px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; font-size: 0.8rem; font-weight: 600; margin: 2px; }

        .grid-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-box {
            background: linear-gradient(160deg, #0C447C, #378ADD);
            color: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(12, 68, 124, 0.15);
        }
        .info-box h3 {
            font-size: 14px;
            font-weight: 400;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-box p {
            font-size: 36px;
            font-weight: 700;
            margin-top: 5px;
        }

        .menu-row {
            display: flex; align-items: center; gap: 15px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: all 0.2s ease;
        }
        .menu-row:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .menu-icon {
            width: 45px; height: 45px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .icon-absensi { background-color: #fdf2f8; color: #ec4899; }
        .menu-text h4 { font-size: 1rem; font-weight: 600; color: var(--text-main); margin-bottom: 2px; }
        .menu-text p { font-size: 0.85rem; color: var(--text-muted); }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand"><i class="fa-solid fa-graduation-cap"></i> Kejar Prestasi</div>

        <div class="user-profile-nav" id="profileToggle">
            <span><?= htmlspecialchars($data_guru['nama'] ?? $username) ?></span>
            <div class="avatar-nav"><?= htmlspecialchars(mb_substr($data_guru['nama'] ?? $username, 0, 1)) ?></div>
            <i class="fa-solid fa-chevron-down chevron-icon"></i>

            <div class="dropdown-menu">
                <?php if ($role === 'admin'): ?>
                    <a class="dropdown-item" href="laporan.php?page=siswa"><i class="fa-solid fa-gear"></i> Panel Admin</a>
                <?php endif; ?>
                <a class="dropdown-item" href="#"><i class="fa-regular fa-circle-user"></i> Ganti Foto Profil</a>
                <a class="dropdown-item" href="#"><i class="fa-solid fa-lock"></i> Ganti Password</a>
                <a class="dropdown-item" href="#"><i class="fa-regular fa-book-open"></i> Panduan Pengguna</a>
                <a class="dropdown-item logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="banner-card">
            <div class="logo-sekolah"><i class="fa-solid fa-star"></i></div>
            <div class="judul-sekolah"><?= htmlspecialchars($infoSekolah->namaSekolah) ?></div>
            <div class="sub-banner">
                <?= htmlspecialchars($infoSekolah->tahunPelajaran) ?> &bull; <?= htmlspecialchars($infoSekolah->semester) ?>
            </div>

            <div class="info-detail-guru">
                <div class="detail-row">
                    <div class="detail-label">Nama Guru</div>
                    <div class="detail-value">: <b><?= htmlspecialchars($data_guru['nama'] ?? $username) ?></b></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Mata Pelajaran</div>
                    <div class="detail-value">: 
                        <?php 
                        if (!empty($data_guru['mapel'])) {
                            $m_arr = explode(", ", $data_guru['mapel']);
                            foreach ($m_arr as $m) {
                                echo "<span class='badge-info-dashboard'>".htmlspecialchars($m)."</span>";
                            }
                        } else {
                            echo "<span style='color: #ef4444; font-size: 13px;'>Belum ditentukan admin</span>";
                        }
                        ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Daftar Kelas</div>
                    <div class="detail-value">: 
                        <?php 
                        if (!empty($data_guru['kelas_diajar'])) {
                            $k_arr = explode(", ", $data_guru['kelas_diajar']);
                            foreach ($k_arr as $k) {
                                echo "<span class='badge-info-dashboard' style='background:#f0fdf4; color:#16a34a; border-color:#bbf7d0;'>".htmlspecialchars($k)."</span>";
                            }
                        } else {
                            echo "<span style='color: #ef4444; font-size: 13px;'>Belum ditentukan admin</span>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-info">
            <div class="info-box">
                <h3>Total Kelas Yang Diampu</h3>
                <p><?= $total_kelas ?></p>
            </div>

            <div class="menu-row" onclick="window.location.href='absensi.php';">
                <div class="menu-icon icon-absensi"><i class="fa-solid fa-user-check"></i></div>
                <div class="menu-text">
                    <h4>Absensi Kehadiran Siswa</h4>
                    <p>Fitur Absensi Digital Siswa</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const profileToggle = document.getElementById('profileToggle');

        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            profileToggle.classList.toggle('open');
        });

        document.addEventListener('click', () => {
            profileToggle.classList.remove('open');
        });

        profileToggle.querySelector('.dropdown-menu').addEventListener('click', (e) => {
            e.stopPropagation();
        });
    </script>
</body>
</html>