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

$isAdmin = ($role_user == 'PF Kanselerai' || $role_user == 'Master Admin');

// --- LOGIKA GRAFIK: JUMLAH DIVISI MINGGU INI ---
$query_grafik = "SELECT u.divisi, COUNT(l.id_log) as total 
                 FROM log_permintaan l 
                 JOIN users u ON l.id_user = u.id_user 
                 WHERE YEARWEEK(l.tgl_input, 1) = YEARWEEK(CURDATE(), 1) 
                 GROUP BY u.divisi";
$res_grafik = mysqli_query($conn, $query_grafik);

$labels = [];
$totals = [];
while ($row = mysqli_fetch_assoc($res_grafik)) {
    $labels[] = $row['divisi'];
    $totals[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Tata Usaha - AnkaraOne</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.8rem;
            font-family: 'Segoe UI', sans-serif;
        }

        /* TOP NAVBAR UTAMA (Ukuran Huruf & Tinggi Sama Persis) */
        .navbar-custom {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e0e0e0;
            height: 65px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
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
        }

        .navbar-custom .nav-link.active {
            color: #0d6efd !important;
            font-weight: 700;
        }

        /* SECONDARY NAVBAR (NAVBAR KHUSUS TU) */
        .navbar-sub {
            background-color: #ffffff;
            border-bottom: 1px solid #eee;
            margin-top: 65px;
            /* Menempel di bawah navbar utama */
            height: 45px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-sub .nav-link {
            color: #0d6efd !important;
            font-weight: 600;
            font-size: 0.7rem;
            /* Ukuran huruf navbar kedua */
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

        /* Layout Adjustment */
        main {
            padding-top: 130px;
            padding-bottom: 40px;
        }

        .user-profile-nav {
            color: #444;
        }

        .vr-dark {
            border-left: 1px solid #ddd;
            height: 24px;
            margin: 0 15px;
        }

        /* Card & Section Styles */
        .card-custom {
            border: 1px solid #eee;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .welcome-section {
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            border: 1px solid #eee;
            margin-bottom: 25px;
        }

        .card-empty {
            border: 1px dashed #ddd;
            border-radius: 12px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            background: transparent;
        }
    </style>
</head>

<body>

    <<nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top px-4">
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
                        <a class="nav-link" href="absensi.php"><i class="bi bi-person-check me-2"></i>Pegawai</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="log_permintaan.php"><i class="bi bi-journal-text me-2"></i>Logbook</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="tu.php"><i class="bi bi-box-seam me-2"></i>Tata Usaha</a>
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
                <a class="nav-link active" href="tu.php"><i class="bi bi-pie-chart-fill me-2"></i>HOME TU</a>
                <a class="nav-link" href="log_permintaan.php"><i class="bi bi-clipboard-data me-2"></i>LOG
                    PERMINTAAN</a>
                <a class="nav-link" href="form_permintaan.php"><i class="bi bi-box-seam me-2"></i>LOG PENDATAAN
                    INVENTARIS</a>
                <a class="nav-link" href="#"><i class="bi bi-pencil-square me-2"></i>LOG PENDATAAN</a>
            </div>
        </div>

        <div class="container" style="max-width: 1200px;">
            <main>
                <div class="welcome-section d-flex justify-content-between align-items-center shadow-sm">
                    <div>
                        <h4 class="fw-bold mb-1">Halo, <?php echo $nama_user; ?>!</h4>
                        <p class="text-muted mb-0">Tampilan dashboard manajemen unit Tata Usaha.</p>
                    </div>
                    <div class="text-end">
                        <h6 class="text-primary fw-bold mb-0"><?php echo date('l, d F Y'); ?></h6>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="card card-custom p-4 h-100">
                            <h6 class="fw-bold mb-4">Jumlah Permintaan per Divisi (Minggu Ini)</h6>
                            <canvas id="divisiChart" height="130"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-custom p-4 h-100 bg-primary text-white">
                            <h6 class="fw-bold opacity-75 small mb-3 text-uppercase">Statistik Minggu Ini</h6>
                            <h1 class="fw-bold mb-0" style="font-size: 3rem;"><?php echo array_sum($totals); ?></h1>
                            <p class="small mb-4 opacity-75">Total Request Dicatat</p>
                            <hr class="opacity-25">
                            <div class="small">
                                <i class="bi bi-info-circle me-1"></i> Data diperbarui otomatis.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card-empty">Fitur Mendatang</div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-empty">Fitur Mendatang</div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-empty">Fitur Mendatang</div>
                    </div>
                </div>
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('divisiChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($totals); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: '#0d6efd',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f0f0f0', drawBorder: false }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        </script>
</body>

</html>