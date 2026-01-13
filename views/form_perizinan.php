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
$divisi_user = $_SESSION['divisi'];

// --- TAMBAHKAN BARIS INI UNTUK MEMPERBAIKI ERROR ---
$role_user = $_SESSION['divisi']; 

// Hak akses Admin khusus PF Kanselerai atau Master Admin
$isAdmin = ($divisi_user == 'PF Kanselerai' || $divisi_user == 'Master Admin');

$tanggal_hari_ini = date('Y-m-d');
$no_arsip = "ARS-" . date('Ymd') . "-" . $id_user . rand(10, 99);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <title>Rekap Izin - AnkaraOne</title>
    <style>
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

        .form-card {
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

        /* Form Labels & Controls */
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }

        .form-control-custom {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 0.8rem;
        }

        .form-control-custom:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05);
            outline: none;
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
            <a class="nav-link" href="absensi.php"><i class="bi bi-pie-chart-fill me-2"></i>DASHBOARD PEGAWAI</a>
            <a class="nav-link" href="verifikasi_muka.php"><i class="bi bi-camera-fill me-2"></i>ABSEN WAJAH</a>
            <a class="nav-link active" href="form_perizinan.php"><i class="bi bi-envelope-paper-fill me-2"></i>PENGAJUAN
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
            <div class="row">
                <div class="col-12">
                    <div class="form-card border-0">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-custom">
                                <li class="breadcrumb-item small"><a href="dashboard.php"
                                        class="text-decoration-none text-muted">Home</a></li>
                                <li class="breadcrumb-item small"><a href="absensi.php"
                                        class="text-decoration-none text-muted">Absensi</a></li>
                                <li class="breadcrumb-item active small text-primary fw-bold" aria-current="page">Form
                                    Perizinan</li>
                            </ol>
                        </nav>

                        <h5 class="fw-bold mb-5 text-dark d-flex align-items-center">
                            <i class="bi bi-envelope-paper me-3 text-primary" style="font-size: 1.5rem;"></i>Pengajuan
                            Perizinan / Cuti Pegawai
                        </h5>

                        <form action="proses_izin.php" method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">No. Arsip</label>
                                    <input type="text" name="no_arsip"
                                        class="form-control form-control-custom bg-light text-muted"
                                        value="<?= $no_arsip; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Perizinan</label>
                                    <select name="jenis" class="form-select form-control-custom" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Cuti">Cuti</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama"
                                        class="form-control form-control-custom bg-light text-muted"
                                        value="<?= $nama_user; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Divisi / Posisi</label>
                                    <input type="text" name="divisi"
                                        class="form-control form-control-custom bg-light text-muted"
                                        value="<?= $divisi_user; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nomor WhatsApp/HP</label>
                                    <input type="number" name="no_hp" class="form-control form-control-custom"
                                        placeholder="Contoh: 552XXXXXX" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Pengajuan</label>
                                    <input type="date" name="tgl_pengajuan"
                                        class="form-control form-control-custom bg-light text-muted"
                                        value="<?= $tanggal_hari_ini; ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="tgl_mulai" id="tgl_mulai"
                                        class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lama (Hari)</label>
                                    <input type="number" name="lama" id="lama" class="form-control form-control-custom"
                                        min="1" placeholder="Contoh: 3" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Selesai (Otomatis)</label>
                                    <input type="date" name="tgl_selesai" id="tgl_selesai"
                                        class="form-control form-control-custom bg-light text-muted" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alasan Perizinan</label>
                                    <textarea name="alasan" class="form-control form-control-custom" rows="3"
                                        placeholder="Jelaskan alasan..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">File Dokumen (Surat Sakit/Lampiran)</label>
                                    <input type="file" name="dokumen" class="form-control form-control-custom">
                                </div>
                                <div class="col-12 mt-5 text-end">
                                    <hr class="mb-4 opacity-10">
                                    <button type="reset" class="btn btn-light px-5 py-2 me-2 fw-bold border"
                                        style="font-size: 0.8rem;">Reset</button>
                                    <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm fw-bold"
                                        style="font-size: 0.8rem;">Kirim Pengajuan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tgl_mulai, #lama').on('change input', function () {
                let tglMulaiStr = $('#tgl_mulai').val();
                let lama = parseInt($('#lama').val());
                if (tglMulaiStr && !isNaN(lama) && lama > 0) {
                    let parts = tglMulaiStr.split('-');
                    let date = new Date(parts[0], parts[1] - 1, parts[2]);
                    date.setDate(date.getDate() + (lama - 1));
                    let year = date.getFullYear();
                    let month = ("0" + (date.getMonth() + 1)).slice(-2);
                    let day = ("0" + date.getDate()).slice(-2);
                    $('#tgl_selesai').val(year + "-" + month + "-" + day);
                }
            });
        });
    </script>
</body>

</html>