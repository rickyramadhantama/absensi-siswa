<?php
include 'config.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$id_guru = $_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['ID_USER'] ?? 0;

$query_guru = $conn->query("SELECT * FROM user WHERE id = '$id_guru'");
$guru = $query_guru->fetch_assoc();

$my_mapel = !empty($guru['mapel']) ? explode(", ", $guru['mapel']) : [];
$my_kelas = !empty($guru['kelas_diajar']) ? explode(", ", $guru['kelas_diajar']) : [];

$get_mapel = $_GET['mapel'] ?? '';
$get_kelas = $_GET['kelas'] ?? '';

if (isset($_POST['simpan_absensi'])) {
    $mapel_input = $_POST['mapel_absensi'];
    $kelas_input = $_POST['kelas_absensi'];
    $tanggal = date('Y-m-d');
    $status_absensi = $_POST['status'] ?? [];

    foreach ($status_absensi as $id_siswa => $status) {
        $ket = $_POST['ket'][$id_siswa] ?? '';
        
        $stmt_cek = $conn->prepare("SELECT id FROM absensi WHERE id_siswa = ? AND tanggal = ? AND mapel = ?");
        $stmt_cek->bind_param("iss", $id_siswa, $tanggal, $mapel_input);
        $stmt_cek->execute();
        $res_cek = $stmt_cek->get_result();

        if ($res_cek->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id_siswa = ? AND tanggal = ? AND mapel = ?");
            $stmt_update->bind_param("ssiss", $status, $ket, $id_siswa, $tanggal, $mapel_input);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO absensi (id_siswa, id_guru, mapel, kelas, tanggal, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $id_siswa, $id_guru, $mapel_input, $kelas_input, $tanggal, $status, $ket);
            $stmt->execute();
            $stmt->close();
        }
        $stmt_cek->close();
    }
    header("Location: absensi.php?mapel=" . urlencode($mapel_input) . "&kelas=" . urlencode($kelas_input) . "&status=sukses");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Mengajar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --bg: #f8fafc; 
            --primary: #4338ca; 
            --text: #1e293b; 
            --border: #e2e8f0; 
        }
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Inter', sans-serif; 
        }
        body { 
            background-color: var(--bg); 
            color: var(--text); 
            padding: 40px 20px; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .header-nav {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 25px;
        }
        .btn-dashboard {
            background: #334155;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: background 0.2s;
        }
        .btn-dashboard:hover {
            background: #1e293b;
        }
        .judul-halaman {
            text-align: center;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .sub-judul-halaman {
            text-align: center;
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 35px;
        }
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 16px; 
            border: 1px solid var(--border); 
            margin-bottom: 25px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01); 
        }
        h3 { 
            font-size: 1.1rem; 
            font-weight: 700; 
            margin-bottom: 20px; 
            color: #1e293b; 
        }
        .btn-grid { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 12px; 
        }
        .btn-pilihan { 
            padding: 14px 24px; 
            background: white; 
            color: #475569; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 600; 
            font-size: 0.9rem; 
            border: 1.5px solid var(--border); 
            transition: all 0.2s; 
        }
        .btn-pilihan:hover { 
            background: #f8fafc; 
            border-color: #cbd5e1; 
        }
        .btn-pilihan.active { 
            background: var(--primary); 
            color: white; 
            border-color: var(--primary); 
            box-shadow: 0 4px 12px rgba(67, 56, 202, 0.15); 
        }
        .info-lembar {
            font-size: 0.9rem; 
            color: #64748b; 
            margin-bottom: 25px;
            background: #f8fafc;
            padding: 12px 20px;
            border-radius: 8px;
            display: inline-block;
            border: 1px solid var(--border);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            padding: 16px; 
            text-align: center; 
            border-bottom: 1px solid var(--border); 
            font-size: 0.9rem;
            transition: background-color 0.2s ease;
        }
        th { 
            background: white; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            color: #64748b; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
        }
        td.no-col {
            color: #64748b;
            font-weight: 500;
        }
        td.nama-col { 
            text-align: left; 
            font-weight: 600; 
            color: #1e293b;
        }
        td.nisn-col {
            color: #64748b;
            font-weight: 500;
        }
        .radio-container {
            display: flex;
            justify-content: center;
        }
        input[type="radio"] {
            transform: scale(1.3);
            cursor: pointer;
        }
        
        input[type="radio"].radio-hadir { accent-color: #16a34a; }
        input[type="radio"].radio-sakit { accent-color: #dc2626; }
        input[type="radio"].radio-izin { accent-color: #64748b; }
        input[type="radio"].radio-alpa { accent-color: #f97316; }

        .row-hadir { background-color: #f0fdf4 !important; }
        .row-sakit { background-color: #fef2f2 !important; }
        .row-izin { background-color: #f1f5f9 !important; }
        .row-alpa { background-color: #fff7ed !important; }

        input[type="text"].input-catatan {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
            font-size: 0.85rem;
            background: #f8fafc;
            transition: all 0.2s;
        }
        input[type="text"].input-catatan:focus {
            border-color: #cbd5e1;
            background: white;
        }
        .footer-actions {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .btn-batal {
            background: #64748b;
            color: white;
        }
        .btn-batal:hover {
            background: #475569;
        }
        .btn-simpan {
            background: #16a34a;
            color: white;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
        }
        .btn-simpan:hover {
            background: #15803d;
        }
        .alert-sukses { 
            background: #dcfce7; 
            color: #15803d; 
            padding: 16px; 
            border-radius: 12px; 
            margin-bottom: 25px; 
            font-weight: 600; 
            font-size: 0.9rem; 
            border: 1px solid #bbf7d0; 
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header-nav">
        <a href="dashboard.php" class="btn-dashboard">Kembali ke Dashboard</a>
    </div>

    <h2 class="judul-halaman">ABSENSI SISWA - KELAS <?= htmlspecialchars($get_kelas ? $get_kelas : '...'); ?></h2>
    <div class="sub-judul-halaman">SMK NEGERI 1 JAKARTA</div>
    
    <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
        <div class="alert-sukses"><i class="fa-solid fa-circle-check"></i> Absensi berhasil disimpan ke dalam database sistem.</div>
    <?php endif; ?>

    <div class="card">
        <h3>Langkah 1: Pilih Mata Pelajaran Yang Diajar</h3>
        <div class="btn-grid">
            <?php if(empty($my_mapel) || (count($my_mapel) == 1 && $my_mapel[0] == '')): ?>
                <p style="color: #ef4444; font-size: 0.9rem;"><i class="fa-solid fa-circle-exclamation"></i> Akun Anda belum dikonfigurasi mapel oleh Admin.</p>
            <?php else: 
                foreach ($my_mapel as $mp): ?>
                    <a href="absensi.php?mapel=<?= urlencode($mp); ?>" class="btn-pilihan <?= $get_mapel === $mp ? 'active' : ''; ?>">
                        <i class="fa-regular fa-bookmark"></i> <?= htmlspecialchars($mp); ?>
                    </a>
                <?php endforeach; 
            endif; ?>
        </div>
    </div>

    <?php if (!empty($get_mapel)): ?>
        <div class="card">
            <h3>Langkah 2: Pilih Kelas untuk Mapel (<?= htmlspecialchars($get_mapel); ?>)</h3>
            <div class="btn-grid">
                <?php if(empty($my_kelas) || (count($my_kelas) == 1 && $my_kelas[0] == '')): ?>
                    <p style="color: #ef4444; font-size: 0.9rem;"><i class="fa-solid fa-circle-exclamation"></i> Akun Anda belum dikonfigurasi kelas mengajar oleh Admin.</p>
                <?php else: 
                    foreach ($my_kelas as $kls): ?>
                        <a href="absensi.php?mapel=<?= urlencode($get_mapel); ?>&kelas=<?= urlencode($kls); ?>" class="btn-pilihan <?= $get_kelas === $kls ? 'active' : ''; ?>">
                            <i class="fa-solid fa-chalkboard-user"></i> <?= htmlspecialchars($kls); ?>
                        </a>
                    <?php endforeach; 
                endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($get_mapel) && !empty($get_kelas)): ?>
        <div class="card">
            <h3>Langkah 3: Lembar Absensi Kelas <?= htmlspecialchars($get_kelas); ?></h3>
            <div class="info-lembar">
                Mata Pelajaran: <b><?= htmlspecialchars($get_mapel); ?></b> &nbsp;|&nbsp; Tanggal Hari Ini: <b><?= date('d-m-Y'); ?></b>
            </div>
            
            <?php
            $stmt_siswa = $conn->prepare("
                SELECT s.id, s.nama, s.nisn, a.status, a.keterangan 
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.id_siswa AND a.tanggal = CURDATE() AND a.mapel = ?
                WHERE s.kelas = ?
                ORDER BY s.nama ASC
            ");
            $stmt_siswa->bind_param("ss", $get_mapel, $get_kelas);
            $stmt_siswa->execute();
            $query_siswa = $stmt_siswa->get_result();
            
            if ($query_siswa->num_rows === 0): ?>
                <p style="text-align: center; color: #94a3b8; padding: 30px; font-size: 0.9rem;"><i class="fa-solid fa-folder-open"></i> Belum ada data siswa di kelas ini pada database.</p>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="mapel_absensi" value="<?= htmlspecialchars($get_mapel); ?>">
                    <input type="hidden" name="kelas_absensi" value="<?= htmlspecialchars($get_kelas); ?>">
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="text-align: left; width: 25%;">NAMA</th>
                                <th style="width: 15%;">NISN</th>
                                <th style="width: 8%;">HADIR</th>
                                <th style="width: 8%;">SAKIT</th>
                                <th style="width: 8%;">IZIN</th>
                                <th style="width: 8%;">ALPA</th>
                                <th style="width: 23%;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($siswa = $query_siswa->fetch_assoc()): 
                                $status_db = strtolower($siswa['status'] ?? '');
                                
                                $row_class = 'row-hadir';
                                if ($status_db === 'sakit') {
                                    $row_class = 'row-sakit';
                                } elseif ($status_db === 'izin') {
                                    $row_class = 'row-izin';
                                } elseif ($status_db === 'alpa') {
                                    $row_class = 'row-alpa';
                                }
                            ?>
                                <tr class="<?= $row_class; ?>">
                                    <td class="no-col"><?= $no++; ?></td>
                                    <td class="nama-col"><?= htmlspecialchars($siswa['nama']); ?></td>
                                    <td class="nisn-col"><?= htmlspecialchars($siswa['nisn'] ?? '-'); ?></td>
                                    <td>
                                        <div class="radio-container">
                                            <input type="radio" name="status[<?= $siswa['id']; ?>]" value="Hadir" class="radio-hadir" onchange="ubahWarnaBaris(this)" <?= ($status_db === 'hadir' || empty($status_db)) ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio-container">
                                            <input type="radio" name="status[<?= $siswa['id']; ?>]" value="Sakit" class="radio-sakit" onchange="ubahWarnaBaris(this)" <?= ($status_db === 'sakit') ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio-container">
                                            <input type="radio" name="status[<?= $siswa['id']; ?>]" value="Izin" class="radio-izin" onchange="ubahWarnaBaris(this)" <?= ($status_db === 'izin') ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio-container">
                                            <input type="radio" name="status[<?= $siswa['id']; ?>]" value="Alpa" class="radio-alpa" onchange="ubahWarnaBaris(this)" <?= ($status_db === 'alpa') ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="ket[<?= $siswa['id']; ?>]" class="input-catatan" value="<?= htmlspecialchars($siswa['keterangan'] ?? ''); ?>" placeholder="Catatan...">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <div class="footer-actions">
                        <a href="absensi.php" class="btn-action btn-batal">Batal</a>
                        <button type="submit" name="simpan_absensi" class="btn-action btn-simpan" onclick="return confirm('Kirim data absensi sekarang?')">Simpan Absensi</button>
                    </div>
                </form>
            <?php 
            endif; 
            $stmt_siswa->close();
            ?>
        </div>
    <?php endif; ?>

</div>

<script>
function ubahWarnaBaris(radio) {
    let baris = radio.closest('tr');
    
    baris.classList.remove('row-hadir', 'row-sakit', 'row-izin', 'row-alpa');
    
    if (radio.value === 'Hadir') {
        baris.classList.add('row-hadir');
    } else if (radio.value === 'Sakit') {
        baris.classList.add('row-sakit');
    } else if (radio.value === 'Izin') {
        baris.classList.add('row-izin');
    } else if (radio.value === 'Alpa') {
        baris.classList.add('row-alpa');
    }
}
</script>

</body>
</html>