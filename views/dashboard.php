<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
$role_user = $_SESSION['divisi'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Dashboard - AnkaraOne</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.8rem;
            font-family: 'Segoe UI', sans-serif;
        }

        /* NAVBAR PUTIH BERSIH (Sesuai SS) */
        .navbar-custom {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e0e0e0;
            height: 65px;
            /* Sedikit lebih tinggi agar lega */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        /* Branding Logo & Teks */
        .navbar-brand {
            color: #333 !important;
            font-size: 1.1rem;
        }

        /* Menu Navigasi Atas */
        .navbar-custom .nav-link {
            color: #666 !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 5px;
            transition: 0.2s;
            display: flex;
            align-items: center;
        }

        .navbar-custom .nav-link i {
            font-size: 1rem;
            color: #888;
        }

        /* Hover & Aktif (Tetap Biru tapi di background putih) */
        .navbar-custom .nav-link:hover {
            color: #0d6efd !important;
        }

        .navbar-custom .nav-link.active {
            color: #0d6efd !important;
            font-weight: 700;
        }

        /* User Profil Sisi Kanan */
        .user-profile-nav {
            color: #444;
        }

        .vr-dark {
            border-left: 1px solid #ddd;
            height: 24px;
            margin: 0 15px;
        }

        main {
            padding-top: 90px;
            padding-bottom: 40px;
        }

        /* Card Dashboard */
        .card {
            border: 1px solid #eee;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            transition: 0.3s;
        }

        .card:hover {
            border-color: #0d6efd;
            transform: translateY(-3px);
        }

        .welcome-section {
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #eee;
            margin-bottom: 25px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top px-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center me-4" href="dashboard.php">
                <img src="logo.PNG" alt="Logo" width="35" height="35" class="me-2 rounded-circle border">
                <span>AnkaraOne</span>
            </a>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="absensi.php"><i class="bi bi-person-check me-2"></i>Pegawai</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="log_permintaan.php"><i class="bi bi-journal-text me-2"></i>Logbook</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tu.php"><i class="bi bi-box-seam me-2"></i>Tata Usaha</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center user-profile-nav">
                    <div class="text-end me-3 d-none d-sm-block">
                        <div class="fw-bold" style="font-size: 0.85rem; line-height: 1.2;"><?php echo $nama_user; ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo $role_user; ?></div>
                    </div>
                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-primary"
                        style="width: 35px; height: 35px; font-size: 0.8rem;">
                        <?php echo strtoupper(substr($nama_user, 0, 2)); ?>
                    </div>
                    <div class="vr-dark"></div>
                    <a href="logout.php"
                        class="btn btn-sm btn-outline-danger border-0 fw-bold d-flex align-items-center">
                        <i class="bi bi-box-arrow-right me-2"></i>LOGOUT
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <main>
            <div class="welcome-section d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <h4 class="fw-bold mb-1">Halo, <?php echo $nama_user; ?>!</h4>
                    <p class="text-muted mb-0">Sistem manajemen AnkaraOne siap digunakan.</p>
                </div>
                <div class="d-none d-md-block">
                    <h6 class="text-primary fw-bold mb-0"><?php echo date('l, d F Y'); ?></h6>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>