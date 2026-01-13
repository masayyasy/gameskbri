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

// Hak akses khusus PF Kanselerai atau Master Admin
$isAdmin = ($role_user == 'PF Kanselerai' || $role_user == 'Master Admin');

// --- DATA GRAFIK PEGAWAI ---
// 1. Total Pegawai
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$total_pegawai = mysqli_fetch_assoc($q_total)['total'];

// 2. Data Divisi untuk Doughnut Chart
$q_divisi = mysqli_query($conn, "SELECT divisi, COUNT(*) as jumlah FROM users GROUP BY divisi");
$divisi_labels = [];
$divisi_counts = [];
while ($row = mysqli_fetch_assoc($q_divisi)) {
    $divisi_labels[] = $row['divisi'];
    $divisi_counts[] = $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <title>Dashboard Pegawai - AnkaraOne</title>
    <style>
        /* DISAMAKAN PERSIS DENGAN REKAP HADIR & FORM IZIN */
        body {
            background-color: #f8f9fa;
            font-size: 0.8rem;
            font-family: 'Segoe UI', sans-serif;
        }

        /* TOP NAVBAR PUTIH BERSIH */
        .navbar-custom {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e0e0e0;
            height: 65px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            z-index: 1050;
        }

        .navbar-brand {
            color: #333 !important;
            font-size: 1.1rem;
        }

        .navbar-custom .nav-link {
            color: #666 !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 5px;
            transition: 0.2s;
            display: flex;
            align-items: center;
        }

        .navbar-custom .nav-link:hover {
            color: #0d6efd !important;
        }

        .navbar-custom .nav-link.active {
            color: #0d6efd !important;
            font-weight: 700;
        }

        /* SECONDARY NAVBAR (KHUSUS ABSENSI) */
        .navbar-sub {
            background-color: #ffffff;
            border-bottom: 1px solid #eee;
            margin-top: 65px;
            height: 45px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1040;
        }

        .navbar-sub .nav-link {
            color: #0d6efd !important;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 15px !important;
            height: 45px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid transparent;
        }

        .navbar-sub .nav-link.active {
            border-bottom: 2px solid #0d6efd;
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

        /* Layout Adjustment for Double Navbar */
        main {
            padding-top: 130px;
            padding-bottom: 40px;
        }

        /* CARD STYLE DISAMAKAN DENGAN TABLE-CARD */
        .table-card,
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            width: 100%;
        }

        /* BREADCRUMB INTERNAL */
        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 25px;
            border-bottom: 1px solid #f1f1f1;
            padding-bottom: 15px;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: ">";
            font-size: 0.7rem;
            color: #ccc;
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
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-2"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="absensi.php"><i class="bi bi-person-check me-2"></i>Pegawai</a>
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

    <div class="navbar-sub shadow-sm">
        <div class="container d-flex justify-content-center">
            <a class="nav-link active" href="absensi.php"><i class="bi bi-pie-chart-fill me-2"></i>DASHBOARD PEGAWAI</a>
            <a class="nav-link" href="verifikasi_muka.php"><i class="bi bi-camera-fill me-2"></i>ABSEN WAJAH</a>
            <a class="nav-link" href="form_perizinan.php"><i class="bi bi-envelope-paper-fill me-2"></i>PENGAJUAN
                IZIN</a>
            <?php if ($isAdmin): ?>
                <a class="nav-link" href="data_pegawai.php"><i class="bi bi-people-fill me-2"></i>DATA PEGAWAI</a>
                <a class="nav-link" href="rekap_hadir.php"><i class="bi bi-file-earmark-text-fill me-2"></i>REKAPAN
                    HADIR</a>
                <a class="nav-link" href="rekap_izin.php"><i class="bi bi-clipboard2-check-fill me-2"></i>REKAPAN IZIN</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container-fluid px-5">
        <main>
            <div class="dashboard-card border-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item small"><a href="dashboard.php"
                                class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item active small text-primary fw-bold" aria-current="page">Dashboard
                            Pegawai</li>
                    </ol>
                </nav>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 p-4 bg-primary text-white shadow-sm h-100"
                            style="border-radius: 12px;">
                            <h6 class="fw-bold opacity-75 small mb-3 text-uppercase">Total Pegawai</h6>
                            <h1 class="fw-bold mb-0" style="font-size: 3.5rem;"><?php echo $total_pegawai; ?></h1>
                            <p class="small mb-4 opacity-75">Personel Aktif AnkaraOne</p>
                            <hr class="opacity-25">
                            <div class="small"><i class="bi bi-shield-check me-1"></i> Terverifikasi Kepegawaian</div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 p-4 h-100 shadow-sm"
                            style="border-radius: 12px; border: 1px solid #eee !important;">
                            <h6 class="fw-bold mb-4">Distribusi Per Divisi</h6>
                            <div class="row align-items-center">
                                <div class="col-md-6 text-center">
                                    <canvas id="divisiDoughnut" height="180"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <div id="chartLegend" class="small"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-4">
                        <div class="card p-4 text-center"
                            style="border: 1px dashed #ddd; border-radius: 12px; background: transparent; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-muted small">Statistik Kehadiran (Soon)</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4 text-center"
                            style="border: 1px dashed #ddd; border-radius: 12px; background: transparent; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-muted small">Statistik Izin (Soon)</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4 text-center"
                            style="border: 1px dashed #ddd; border-radius: 12px; background: transparent; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-muted small">Fitur Baru</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('divisiDoughnut').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($divisi_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($divisi_counts); ?>,
                    backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, font: { size: 10 } } } },
                cutout: '70%'
            }
        });
    </script>
</body>

</html>