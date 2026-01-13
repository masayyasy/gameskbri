<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
include '../config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
$role_user = $_SESSION['divisi'];

// --- 1. PROSES REQUEST BARANG ---
if (isset($_POST['proses_request'])) {
    $id_atk = $_POST['id_atk'];
    $nama_req = mysqli_real_escape_string($conn, $_POST['nama_pengambil']);
    $jumlah = $_POST['jumlah'];
    $tgl = date('Y-m-d');
    $jam = date('H:i:s');

    mysqli_query($conn, "INSERT INTO pengambilan_atk (id_user, id_atk, nama_pengambil, jumlah, status, tanggal_ambil, jam_ambil) 
                         VALUES ('$id_user', '$id_atk', '$nama_req', '$jumlah', 'Pending', '$tgl', '$jam')");
    echo "<script>alert('Permintaan berhasil dikirim!'); window.location='atk.php';</script>";
}

// --- 2. PROSES APPROVAL ---
if (isset($_GET['action']) && $role_user == 'PF Kanselerai') {
    $id_log = $_GET['id'];
    $action = $_GET['action'];
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengambilan_atk WHERE id_ambil = '$id_log'"));
    
    if ($action == 'Setujui') {
        mysqli_query($conn, "UPDATE atk_barang SET stok = stok - {$data['jumlah']} WHERE id_barang = '{$data['id_atk']}'");
        mysqli_query($conn, "UPDATE pengambilan_atk SET status = 'Disetujui' WHERE id_ambil = '$id_log'");
    } elseif ($action == 'Tolak') {
        mysqli_query($conn, "UPDATE pengambilan_atk SET status = 'Ditolak' WHERE id_ambil = '$id_log'");
    }
    header("Location: atk.php");
}

// --- 3. TAMBAH BARANG ---
if (isset($_POST['tambah_barang'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok = $_POST['stok_awal'];
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    mysqli_query($conn, "INSERT INTO atk_barang (deskripsi, stok, satuan, kode_barang, kode_urut) VALUES ('$nama', '$stok', '$satuan', '-', '-')");
    header("Location: atk.php");
}

// --- 4. HAPUS BARANG ---
if (isset($_GET['delete_id'])) {
    mysqli_query($conn, "DELETE FROM atk_barang WHERE id_barang = '{$_GET['delete_id']}'");
    header("Location: atk.php");
}

// --- 5. UPDATE STOK INLINE ---
if (isset($_POST['update_stok_inline'])) {
    mysqli_query($conn, "UPDATE atk_barang SET stok = '{$_POST['value']}' WHERE id_barang = '{$_POST['id']}'");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>ATK - AnkaraOne</title>
    <style>
        body { background-color: #fcfcfc; font-size: 0.8rem; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: #ffffff; border-right: 1px solid #eee; position: fixed; top: 56px; left: 0; width: 200px; z-index: 100; }
        .nav-link { color: #666; padding: 10px 20px; border-radius: 0 25px 25px 0; margin-right: 10px; }
        .nav-link.active { background: #f0f4ff; color: #0d6efd; fw-bold; }
        main { margin-left: 200px; padding: 80px 25px 25px 25px; }
        .card { border: 1px solid #eee; border-radius: 12px; background: #fff; box-shadow: none; margin-bottom: 20px; }
        .card-header { background: #fff; border-bottom: 1px solid #eee; font-weight: 600; color: #333; }
        .table-stok-container { height: calc(100vh - 180px); overflow-y: auto; }
        .table thead th { background: #fafafa; color: #888; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; border-bottom: 1px solid #eee; }
        .table td { vertical-align: middle; border-bottom: 1px solid #f9f9f9; padding: 10px 8px; }
        .editable-stok { cursor: pointer; color: #0d6efd; text-decoration: underline dashed; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #eee; background: #fdfdfd; }
        .btn-primary { border-radius: 8px; padding: 8px; background: #0d6efd; border: none; }
        .btn-warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; border-radius: 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top border-bottom px-4" style="height: 56px; background-color: #333333; border-color: #444 !important;">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
            <img src="logo.PNG" alt="Logo" width="30" height="30" class="me-2 rounded-circle border border-secondary">
            <span class="text-white" style="letter-spacing: 0.5px;">AnkaraOne</span>
        </a>

        <div class="ms-auto d-flex align-items-center">
            <div class="d-flex flex-column align-items-end me-3">
                <span class="text-light opacity-75" style="font-size: 0.65rem; line-height: 1;">Logged in as:</span>
                <span class="text-white fw-bold" style="font-size: 0.8rem;"><?php echo $nama_user; ?></span>
            </div>
            <div class="vr bg-light opacity-25 me-3" style="height: 25px;"></div>
            <a href="logout.php" class="btn btn-sm btn-outline-danger border-0 fw-bold" style="font-size: 0.75rem;">LOGOUT</a>
        </div>
    </div>
</nav>
<div class="container-fluid px-0">
        <div class="row g-0">
            <nav class="sidebar p-3 d-none d-md-block">
                <div class="text-muted small fw-bold mb-3 ms-2 text-uppercase" style="font-size: 0.65rem;">Menu</div>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link active" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="absensi.php"><i class="bi bi-geo-alt me-2"></i> Absensi</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="#"><i class="bi bi-journal-text me-2"></i> Logbook</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="#"><i class="bi bi-envelope me-2"></i> Perizinan</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="tu.php"><i class="bi bi-box-seam me-2"></i> Tata Usaha</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="#"><i class="bi bi-truck me-2"></i> GPS Mobil</a>
                    </li>
                </ul>
            </nav>

        <main class="col">
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                            <span>Daftar Stok Barang</span>
                            <div class="input-group input-group-sm w-50">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchInput" class="form-control bg-light border-0" placeholder="Cari nama barang...">
                            </div>
                        </div>
                        <div class="card-body p-0 table-stok-container">
                            <table class="table table-hover" id="atkTable">
                                <thead>
                                    <tr>
                                        <th width="15%">Kode</th>
                                        <th width="40%">Nama Barang</th>
                                        <th width="15%">Satuan</th>
                                        <?php if ($role_user == 'PF Kanselerai' || $role_user == 'Master Admin'): ?>
                                            <th width="10%" class="text-center">Stok</th>
                                            <th width="20%" class="text-center">Keterangan</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $res = mysqli_query($conn, "SELECT * FROM atk_barang ORDER BY deskripsi ASC");
                                    while ($row = mysqli_fetch_assoc($res)): ?>
                                        <tr>
                                            <td class="text-muted"><?php echo $row['kode_barang']; ?></td>
                                            <td class="nama-brg fw-medium text-dark"><?php echo $row['deskripsi']; ?></td>
                                            <td class="text-muted"><?php echo $row['satuan']; ?></td>
                                            <?php if ($role_user == 'PF Kanselerai' || $role_user == 'Master Admin'): ?>
                                                <td class="text-center">
                                                    <span class="editable-stok fw-bold" data-id="<?php echo $row['id_barang']; ?>" ondblclick="editStok(this)">
                                                        <?php echo $row['stok']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="atk.php?delete_id=<?php echo $row['id_barang']; ?>" class="btn btn-sm text-danger px-2" onclick="return confirm('Hapus barang ini?')">
                                                        <i class="bi bi-trash3 me-1"></i> Hapus
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card border-0 bg-white shadow-sm">
                        <div class="card-header border-0 pb-0">Form Pengambilan</div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold mb-1">Nama Pengambil</label>
                                    <input type="text" name="nama_pengambil" class="form-control" placeholder="Siapa yang mengambil?" required>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold mb-1">Pilih Barang</label>
                                    <select name="id_atk" class="form-select" required>
                                        <option value="">-- Pilih Barang di Stok --</option>
                                        <?php
                                        $opt = mysqli_query($conn, "SELECT * FROM atk_barang WHERE stok > 0 ORDER BY deskripsi ASC");
                                        while ($o = mysqli_fetch_assoc($opt)) echo "<option value='{$o['id_barang']}'>{$o['deskripsi']} ({$o['satuan']})</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold mb-1">Jumlah (Qty)</label>
                                    <input type="number" name="jumlah" class="form-control" placeholder="0" required min="1">
                                </div>
                                <button type="submit" name="proses_request" class="btn btn-primary w-100 fw-bold">Kirim Permintaan</button>
                            </form>
                        </div>
                    </div>

                    <?php if ($role_user == 'PF Kanselerai' || $role_user == 'Master Admin'): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0 pb-0 text-warning">Input Barang Baru</div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-2"><input type="text" name="nama_barang" class="form-control" placeholder="Nama Barang" required></div>
                                <div class="row g-2 mb-3">
                                    <div class="col"><input type="number" name="stok_awal" class="form-control" placeholder="Stok" required></div>
                                    <div class="col"><input type="text" name="satuan" class="form-control" placeholder="Satuan" required></div>
                                </div>
                                <button type="submit" name="tambah_barang" class="btn btn-warning w-100 fw-bold">Tambah ke Gudang</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0 pb-0">Aktivitas Terakhir</div>
                        <div class="card-body p-0 table-log-container">
                            <table class="table table-sm" style="font-size: 0.7rem;">
                                <thead>
                                    <tr><th>User</th><th>Barang</th><th>Qty</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $logs = mysqli_query($conn, "SELECT p.*, m.deskripsi, u.divisi FROM pengambilan_atk p JOIN atk_barang m ON p.id_atk = m.id_barang JOIN users u ON p.id_user = u.id_user ORDER BY p.id_ambil DESC LIMIT 10");
                                    while ($l = mysqli_fetch_assoc($logs)): ?>
                                        <tr>
                                            <td><b class="text-primary"><?php echo $l['divisi']; ?></b><br><?php echo substr($l['nama_pengambil'],0,10); ?></td>
                                            <td><?php echo substr($l['deskripsi'],0,15); ?></td>
                                            <td class="text-center"><?php echo $l['jumlah']; ?></td>
                                            <td>
                                                <?php if ($l['status'] == 'Pending' && $role_user == 'PF Kanselerai'): ?>
                                                    <a href="atk.php?action=Setujui&id=<?php echo $l['id_ambil']; ?>" class="text-success fw-bold text-decoration-none">Approve</a>
                                                <?php else: ?>
                                                    <span class="text-<?php echo $l['status']=='Disetujui'?'success':'muted'; ?>"><?php echo $l['status']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Pencarian
    document.getElementById('searchInput').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#atkTable tbody tr');
        rows.forEach(row => {
            let text = row.querySelector('.nama-brg').textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Inline Edit Stok
    function editStok(element) {
        let id = element.getAttribute('data-id');
        let oldValue = element.innerText;
        let input = document.createElement('input');
        input.type = 'number';
        input.value = oldValue;
        input.className = 'form-control form-control-sm text-center';
        input.style.width = '60px';
        element.innerText = '';
        element.appendChild(input);
        input.focus();
        input.onblur = function() { saveStok(); };
        input.onkeydown = function(e) { if(e.key === 'Enter') saveStok(); };
        function saveStok() {
            let newValue = input.value;
            element.innerText = newValue;
            let formData = new FormData();
            formData.append('update_stok_inline', '1');
            formData.append('id', id);
            formData.append('value', newValue);
            fetch('atk.php', { method: 'POST', body: formData });
        }
    }
</script>
</body>
</html>