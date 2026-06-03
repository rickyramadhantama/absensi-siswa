<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'siswa';

$opsi_mapel_global = [
    "Pemrograman Web", 
    "Pemrograman Berorientasi Objek (PBO)", 
    "Dasar-Dasar PPLG", 
    "Basis Data", 
    "Informatika",
    "Matematika",
    "Bahasa Indonesia",
    "Bahasa Inggris"
];

$opsi_kelas = [
    "X-PPLG", "XI-PPLG", "XII-PPLG",
    "X-HOTEL", "XI-HOTEL", "XII-HOTEL",
    "X-PEMASARAN", "XI-PEMASARAN", "XII-PEMASARAN"
];

if ($page === 'siswa') {
    if (isset($_POST['tambah_siswa'])) {
        $nama = trim($_POST['nama']);
        $nisn = trim($_POST['nisn']);
        $kelas = strtoupper(trim($_POST['kelas']));

        if (!empty($nama) && !empty($nisn) && !empty($kelas)) {
            $stmt = $conn->prepare("INSERT INTO siswa (nama, nisn, kelas) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama, $nisn, $kelas);
            $stmt->execute();
            $stmt->close();
            header("Location: laporan.php?page=siswa&status=ditambahkan");
            exit;
        }
    }

    if (isset($_POST['update_siswa'])) {
        $id = $_POST['id_siswa'];
        $nama = trim($_POST['nama']);
        $nisn = trim($_POST['nisn']);
        $kelas = strtoupper(trim($_POST['kelas']));

        if (!empty($id) && !empty($nama) && !empty($nisn) && !empty($kelas)) {
            $stmt = $conn->prepare("UPDATE siswa SET nama = ?, nisn = ?, kelas = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama, $nisn, $kelas, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: laporan.php?page=siswa&status=diubah");
            exit;
        }
    }

    if (isset($_GET['hapus_siswa'])) {
        $id_hapus = $_GET['hapus_siswa'];
        $stmt = $conn->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->bind_param("i", $id_hapus);
        $stmt->execute();
        $stmt->close();
        header("Location: laporan.php?page=siswa&status=dihapus");
        exit;
    }

    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $keyword = "%" . $search . "%";
        $stmt_siswa = $conn->prepare("SELECT * FROM siswa WHERE nama LIKE ? OR nisn LIKE ? ORDER BY kelas ASC, nama ASC");
        $stmt_siswa->bind_param("ss", $keyword, $keyword);
        $stmt_siswa->execute();
        $query_siswa = $stmt_siswa->get_result();
    } else {
        $query_siswa = $conn->query("SELECT * FROM siswa ORDER BY kelas ASC, nama ASC");
    }
}

if ($page === 'guru') {
    if (isset($_POST['tambah_guru'])) {
        $nama_guru = trim($_POST['nama_guru']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']); 
        $role = 'guru';

        $mapel_kelas_input = $_POST['mapel_kelas'] ?? [];
        $saved_mapel = [];
        $saved_kelas = [];

        foreach ($mapel_kelas_input as $m_name => $kelas_arr) {
            if (!empty($kelas_arr)) {
                $saved_mapel[] = $m_name;
                foreach ($kelas_arr as $k_name) {
                    if (!in_array($k_name, $saved_kelas)) {
                        $saved_kelas[] = $k_name;
                    }
                }
            }
        }

        $mapel_string = implode(", ", $saved_mapel);
        $kelas_string = implode(", ", $saved_kelas);
        $mapping_json = json_encode($mapel_kelas_input);

        if (!empty($nama_guru) && !empty($username) && !empty($password)) {
            $stmt = $conn->prepare("INSERT INTO user (username, password, nama, mapel, kelas_diajar, mapping_tugas, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $password, $nama_guru, $mapel_string, $kelas_string, $mapping_json, $role);
            $stmt->execute();
            $stmt->close();
            header("Location: laporan.php?page=guru&status=ditambahkan");
            exit;
        }
    }

    if (isset($_POST['update_guru'])) {
        $id_guru = $_POST['id_guru'];
        $nama_guru = trim($_POST['nama_guru']);
        $username = trim($_POST['username']);
        
        $mapel_kelas_input = $_POST['mapel_kelas'] ?? [];
        $saved_mapel = [];
        $saved_kelas = [];

        foreach ($mapel_kelas_input as $m_name => $kelas_arr) {
            if (!empty($kelas_arr)) {
                $saved_mapel[] = $m_name;
                foreach ($kelas_arr as $k_name) {
                    if (!in_array($k_name, $saved_kelas)) {
                        $saved_kelas[] = $k_name;
                    }
                }
            }
        }

        $mapel_string = implode(", ", $saved_mapel);
        $kelas_string = implode(", ", $saved_kelas);
        $mapping_json = json_encode($mapel_kelas_input);

        if (!empty($_POST['password'])) {
            $password = trim($_POST['password']); 
            $stmt = $conn->prepare("UPDATE user SET nama = ?, username = ?, password = ?, mapel = ?, kelas_diajar = ?, mapping_tugas = ? WHERE id = ? AND role = 'guru'");
            $stmt->bind_param("ssssssi", $nama_guru, $username, $password, $mapel_string, $kelas_string, $mapping_json, $id_guru);
        } else {
            $stmt = $conn->prepare("UPDATE user SET nama = ?, username = ?, mapel = ?, kelas_diajar = ?, mapping_tugas = ? WHERE id = ? AND role = 'guru'");
            $stmt->bind_param("sssssi", $nama_guru, $username, $mapel_string, $kelas_string, $mapping_json, $id_guru);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: laporan.php?page=guru&status=diubah");
        exit;
    }

    if (isset($_GET['hapus_guru'])) {
        $id_hapus = $_GET['hapus_guru'];
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ? AND role = 'guru'");
        $stmt->bind_param("i", $id_hapus);
        $stmt->execute();
        $stmt->close();
        header("Location: laporan.php?page=guru&status=dihapus");
        exit;
    }

    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $keyword = "%" . $search . "%";
        $stmt_guru = $conn->prepare("SELECT * FROM user WHERE role = 'guru' AND (nama LIKE ? OR mapel LIKE ?) ORDER BY id DESC");
        $stmt_guru->bind_param("ss", $keyword, $keyword);
        $stmt_guru->execute();
        $query_guru = $stmt_guru->get_result();
    } else {
        $query_guru = $conn->query("SELECT * FROM user WHERE role = 'guru' ORDER BY id DESC");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Panel - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; color: #334155; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e293b; color: white; position: fixed; top: 0; bottom: 0; left: 0; padding: 25px 15px; display: flex; flex-direction: column; gap: 30px; }
        .sidebar-brand h3 { text-align: center; font-size: 20px; letter-spacing: 1px; color: #38bdf8; border-bottom: 1px solid #334155; padding-bottom: 15px; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 8px; flex: 1; }
        .menu-item { display: block; padding: 12px 15px; color: #94a3b8; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px; transition: all 0.2s ease; }
        .menu-item:hover, .menu-item.active { background: #334155; color: white; }
        .btn-logout { display: block; padding: 12px 15px; background: #ef4444; color: white; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px; text-align: center; margin-top: auto; transition: background 0.2s; }
        .btn-logout:hover { background: #dc2626; }
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .header-section { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .header-section h2 { font-size: 26px; color: #0f172a; }
        .action-bar { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; }
        .search-container { flex: 1; display: flex; gap: 10px; }
        .search-input { flex: 1; padding: 11px 15px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 14px; background: white; }
        .btn-search { background: #475569; color: white; padding: 0 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; }
        .btn-search:hover { background: #334155; }
        .btn-clear { background: #e2e8f0; color: #475569; padding: 0 15px; border-radius: 8px; display: inline-flex; align-items: center; text-decoration: none; font-size: 13px; font-weight: 600; }
        .card-table { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow-x: auto; width: 100%; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 15px; text-align: left; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 12px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; font-size: 14px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; text-decoration: none; transition: all 0.2s ease; }
        .btn-tambah-trigger { background: #2563eb; color: white; padding: 12px 20px; }
        .btn-tambah-trigger:hover { background: #1d4ed8; }
        .btn-primary { background: #2563eb; color: white; width: 100%; }
        .btn-edit { background: #eab308; color: white; padding: 6px 12px; margin-right: 5px; border-radius: 6px; font-size: 13px;}
        .btn-danger { background: #ef4444; color: white; padding: 6px 12px; border-radius: 6px; font-size: 13px;}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #475569; text-align: left; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; font-size: 14px; }
        .mapping-container { border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; max-height: 300px; overflow-y: auto; background: #f8fafc; }
        .mapel-block { background: white; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px; margin-bottom: 12px; }
        .mapel-header { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; color: #1e293b; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0; margin-bottom: 8px; }
        .kelas-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; padding-left: 22px; }
        .kelas-item { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #475569; cursor: pointer; }
        .badge-kelas { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; display: inline-block; margin: 2px; }
        .badge-kelas-guru { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-mapel { background: #eff6ff; color: #1d4ed8; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 12px; display: inline-block; margin: 2px; border: 1px solid #bfdbfe; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 999; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 520px; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .close-modal { position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 20px; font-weight: bold; color: #94a3b8; }
        .tugas-wrapper { display: flex; flex-direction: column; gap: 4px; font-size: 13px; }
        .tugas-line { display: block; margin-bottom: 4px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <h3>PANEL ADMIN</h3>
        </div>
        <div class="sidebar-menu">
            <a href="laporan.php?page=siswa" class="menu-item <?= $page === 'siswa' ? 'active' : ''; ?>">Data Siswa Master</a>
            <a href="laporan.php?page=guru" class="menu-item <?= $page === 'guru' ? 'active' : ''; ?>">Kelola Akun Guru</a>
            <a href="dashboard.php" class="menu-item">Kembali Ke Dashboard</a>
        </div>
        <a href="logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">Logout</a>
    </div>

    <div class="main-content">
        
        <?php if ($page === 'siswa'): ?>
            <div class="header-section">
                <h2>Manajemen Data Siswa Global</h2>
                <button class="btn btn-tambah-trigger" onclick="bukaModal('modalTambahSiswa')">+ Tambah Siswa Baru</button>
            </div>

            <div class="action-bar">
                <form method="GET" action="laporan.php" class="search-container">
                    <input type="hidden" name="page" value="siswa">
                    <input type="text" name="search" class="search-input" placeholder="Cari nama siswa atau NISN..." value="<?= htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="btn-search">Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="laporan.php?page=siswa" class="btn-clear">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Siswa</th>
                            <th style="width: 20%;">NISN</th>
                            <th style="width: 15%;">Kelas</th>
                            <th style="width: 20%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($query_siswa->num_rows === 0): ?>
                            <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 30px;">Data siswa tidak ditemukan.</td></tr>
                        <?php else: 
                            while($row = $query_siswa->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['nisn']); ?></td>
                                <td><span class="badge-kelas"><?= htmlspecialchars($row['kelas']); ?></span></td>
                                <td style="text-align: center;">
                                    <button class="btn btn-edit" onclick="bukaModalEditSiswa(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama']); ?>', '<?= htmlspecialchars($row['nisn']); ?>', '<?= htmlspecialchars($row['kelas']); ?>')">Edit</button>
                                    <a href="laporan.php?page=siswa&hapus_siswa=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="modalTambahSiswa" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="tutupModal('modalTambahSiswa')">&times;</span>
                    <h3 style="margin-bottom: 20px;">Tambah Siswa Baru</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Ricky Ramadhan" required>
                        </div>
                        <div class="form-group">
                            <label>NISN</label>
                            <input type="text" name="nisn" class="form-control" placeholder="Contoh: 006123456" required>
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Contoh: X-PPLG" required>
                        </div>
                        <button type="submit" name="tambah_siswa" class="btn btn-primary">Simpan Siswa</button>
                    </form>
                </div>
            </div>

            <div id="modalEditSiswa" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="tutupModal('modalEditSiswa')">&times;</span>
                    <h3 style="margin-bottom: 20px;">Ubah Data Siswa</h3>
                    <form method="POST">
                        <input type="hidden" name="id_siswa" id="edit_siswa_id">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" id="edit_siswa_nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>NISN</label>
                            <input type="text" name="nisn" id="edit_siswa_nisn" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <input type="text" name="kelas" id="edit_siswa_kelas" class="form-control" required>
                        </div>
                        <button type="submit" name="update_siswa" class="btn btn-primary" style="background: #eab308;">Update Data</button>
                    </form>
                </div>
            </div>

        <?php elseif ($page === 'guru'): ?>
            <div class="header-section">
                <h2>Manajemen Akun Guru (Tugas Spesifik)</h2>
                <button class="btn btn-tambah-trigger" onclick="bukaModal('modalTambahGuru')">+ Tambah Guru Baru</button>
            </div>

            <div class="action-bar">
                <form method="GET" action="laporan.php" class="search-container">
                    <input type="hidden" name="page" value="guru">
                    <input type="text" name="search" class="search-input" placeholder="Cari nama guru..." value="<?= htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="btn-search">Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="laporan.php?page=guru" class="btn-clear">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Guru</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Tugas Mengajar (Mapel & Kelas)</th>
                            <th style="width: 15%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($query_guru->num_rows === 0): ?>
                            <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;">Data guru tidak ditemukan.</td></tr>
                        <?php else: 
                            while($row = $query_guru->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($row['nama']); ?></td>
                                <td><code><?= htmlspecialchars($row['username']); ?></code></td>
                                <td><b style="color: #10b981;"><?= htmlspecialchars($row['password']); ?></b></td>
                                <td>
                                    <div class="tugas-wrapper">
                                        <?php 
                                        $mapping_tugas = json_decode($row['mapping_tugas'] ?? '{}', true);
                                        if(!empty($mapping_tugas)) {
                                            foreach($mapping_tugas as $mpl => $kelas_arr) {
                                                if(!empty($kelas_arr)) {
                                                    echo "<div class='tugas-line'>";
                                                    echo "<span class='badge-mapel'>".htmlspecialchars($mpl)."</span> : ";
                                                    foreach($kelas_arr as $kls) {
                                                        echo "<span class='badge-kelas badge-kelas-guru'>".htmlspecialchars($kls)."</span>";
                                                    }
                                                    echo "</div>";
                                                }
                                            }
                                        } else {
                                            echo "<span style='color:#94a3b8; font-size:12px;'>Belum ada tugas mengajar</span>";
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <button class="btn btn-edit" onclick="bukaModalEditGuru(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama']); ?>', '<?= htmlspecialchars($row['username']); ?>', '<?= htmlspecialchars($row['mapping_tugas'] ?? '{}'); ?>')">Edit</button>
                                    <a href="laporan.php?page=guru&hapus_guru=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus akun guru ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="modalTambahGuru" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="tutupModal('modalTambahGuru')">&times;</span>
                    <h3 style="margin-bottom: 20px;">Tambah Guru Baru</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap Guru</label>
                            <input type="text" name="nama_guru" class="form-control" placeholder="Contoh: Faqih, S.Kom." required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Untuk login guru" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Atur Mata Pelajaran & Kelas yang Diajar</label>
                            <div class="mapping-container">
                                <?php foreach($opsi_mapel_global as $index => $mg): ?>
                                    <div class="mapel-block">
                                        <div class="mapel-header">
                                            <input type="checkbox" id="add_mp_<?= $index ?>" onchange="toggleKelasBlock('add', <?= $index ?>)">
                                            <label for="add_mp_<?= $index ?>"><?= $mg ?></label>
                                        </div>
                                        <div class="kelas-grid" id="add_kelas_block_<?= $index ?>" style="opacity: 0.5; pointer-events: none;">
                                            <?php foreach($opsi_kelas as $ok): ?>
                                                <label class="kelas-item">
                                                    <input type="checkbox" name="mapel_kelas[<?= $mg ?>][]" value="<?= $ok ?>" class="add-kelas-cb-<?= $index ?>"> <?= $ok ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" name="tambah_guru" class="btn btn-primary">Simpan Akun</button>
                    </form>
                </div>
            </div>

            <div id="modalEditGuru" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="tutupModal('modalEditGuru')">&times;</span>
                    <h3 style="margin-bottom: 20px;">Ubah Data Guru</h3>
                    <form method="POST">
                        <input type="hidden" name="id_guru" id="edit_guru_id">
                        <div class="form-group">
                            <label>Nama Lengkap Guru</label>
                            <input type="text" name="nama_guru" id="edit_guru_nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" id="edit_guru_username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password (Kosongkan jika tidak diganti)</label>
                            <input type="text" name="password" class="form-control" placeholder="Masukkan password baru">
                        </div>
                        
                        <div class="form-group">
                            <label>Atur Mata Pelajaran & Kelas yang Diajar</label>
                            <div class="mapping-container">
                                <?php foreach($opsi_mapel_global as $index => $mg): ?>
                                    <div class="mapel-block">
                                        <div class="mapel-header">
                                            <input type="checkbox" id="edit_mp_<?= $index ?>" data-mapel-name="<?= htmlspecialchars($mg) ?>" class="edit-mp-trigger" onchange="toggleKelasBlock('edit', <?= $index ?>)">
                                            <label for="edit_mp_<?= $index ?>"><?= $mg ?></label>
                                        </div>
                                        <div class="kelas-grid" id="edit_kelas_block_<?= $index ?>" style="opacity: 0.5; pointer-events: none;">
                                            <?php foreach($opsi_kelas as $ok): ?>
                                                <label class="kelas-item">
                                                    <input type="checkbox" name="mapel_kelas[<?= $mg ?>][]" value="<?= $ok ?>" data-kelas-name="<?= htmlspecialchars($ok) ?>" class="edit-kelas-cb-<?= $index ?> edit-all-kelas-cb"> <?= $ok ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" name="update_guru" class="btn btn-primary" style="background: #eab308;">Update Akun</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function bukaModal(idModal) {
            document.getElementById(idModal).style.display = 'flex';
        }
        
        function tutupModal(idModal) {
            document.getElementById(idModal).style.display = 'none';
        }

        function toggleKelasBlock(prefix, index) {
            let mpCheckbox = document.getElementById(prefix + '_mp_' + index);
            let kelasBlock = document.getElementById(prefix + '_kelas_block_' + index);
            let kelasCheckboxes = document.querySelectorAll('.' + prefix + '-kelas-cb-' + index);

            if (mpCheckbox.checked) {
                kelasBlock.style.opacity = '1';
                kelasBlock.style.pointerEvents = 'auto';
            } else {
                kelasBlock.style.opacity = '0.5';
                kelasBlock.style.pointerEvents = 'none';
                kelasCheckboxes.forEach(cb => cb.checked = false);
            }
        }

        function bukaModalEditSiswa(id, nama, nisn, kelas) {
            document.getElementById('edit_siswa_id').value = id;
            document.getElementById('edit_siswa_nama').value = nama;
            document.getElementById('edit_siswa_nisn').value = nisn;
            document.getElementById('edit_siswa_kelas').value = kelas;
            bukaModal('modalEditSiswa');
        }

        function bukaModalEditGuru(id, nama, username, mapping_tugas_json) {
            document.getElementById('edit_guru_id').value = id;
            document.getElementById('edit_guru_nama').value = nama;
            document.getElementById('edit_guru_username').value = username;
            
            let mapping = {};
            try {
                mapping = JSON.parse(mapping_tugas_json);
            } catch(e) {
                mapping = {};
            }

            let mpTriggers = document.querySelectorAll('.edit-mp-trigger');
            mpTriggers.forEach((mpCb, index) => {
                let mapelName = mpCb.getAttribute('data-mapel-name');
                let kelasBlock = document.getElementById('edit_kelas_block_' + index);
                let kelasCbs = document.querySelectorAll('.edit-kelas-cb-' + index);

                if (mapping.hasOwnProperty(mapelName) && mapping[mapelName].length > 0) {
                    mpCb.checked = true;
                    kelasBlock.style.opacity = '1';
                    kelasBlock.style.pointerEvents = 'auto';

                    let activeKelas = mapping[mapelName];
                    kelasCbs.forEach(kCb => {
                        let kelasName = kCb.getAttribute('data-kelas-name');
                        if (activeKelas.includes(kelasName)) {
                            kCb.checked = true;
                        } else {
                            kCb.checked = false;
                        }
                    });
                } else {
                    mpCb.checked = false;
                    kelasBlock.style.opacity = '0.5';
                    kelasBlock.style.pointerEvents = 'none';
                    kelasCbs.forEach(kCb => kCb.checked = false);
                }
            });

            bukaModal('modalEditGuru');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        };
    </script>
</body>
</html>