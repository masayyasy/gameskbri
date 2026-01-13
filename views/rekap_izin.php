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

// Ambil data perizinan
if ($isAdmin) {
    $query = "SELECT p.*, u.nama_lengkap, u.divisi FROM perizinan p 
              JOIN users u ON p.id_user = u.id_user 
              ORDER BY p.tgl_mulai DESC";
} else {
    $query = "SELECT p.*, u.nama_lengkap, u.divisi FROM perizinan p 
              JOIN users u ON p.id_user = u.id_user 
              WHERE p.id_user = '$id_user' 
              ORDER BY p.tgl_mulai DESC";
}
$result = mysqli_query($conn, $query);
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

        .table-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
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

        /* Badge Colors */
        .badge-izin {
            background-color: #fff3cd;
            color: #856404;
            border: none;
        }

        .badge-sakit {
            background-color: #f8d7da;
            color: #721c24;
            border: none;
        }

        .badge-cuti {
            background-color: #cfe2ff;
            color: #084298;
            border: none;
        }

        /* Excel Filter Styling */
        .filter-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .filter-icon {
            color: #bbb;
            margin-left: 5px;
            font-size: 0.8rem;
        }

        .filter-icon.active {
            color: #0d6efd;
        }

        .excel-filter-card {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            width: 230px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            padding: 10px;
            top: 25px;
            left: 0;
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
            <a class="nav-link" href="form_perizinan.php"><i class="bi bi-envelope-paper-fill me-2"></i>PENGAJUAN
                IZIN</a>
            <?php if ($isAdmin): ?>
                <a class="nav-link" href="data_pegawai.php"><i class="bi bi-people-fill me-2"></i>DATA PEGAWAI</a>
                <a class="nav-link" href="rekap_hadir.php"><i class="bi bi-file-earmark-text-fill me-2"></i>REKAPAN
                    HADIR</a>
                <a class="nav-link active" href="rekap_izin.php"><i class="bi bi-clipboard2-check-fill me-2"></i>REKAPAN
                    IZIN</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container-fluid px-5">
        <main>
            <div class="table-card border-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item small"><a href="dashboard.php"
                                class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item small"><a href="absensi.php"
                                class="text-decoration-none text-muted">Absensi</a></li>
                        <li class="breadcrumb-item active small text-primary fw-bold" aria-current="page">Rekap Izin &
                            Cuti</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark">Data Pengajuan Perizinan</h4>
                    <a href="form_perizinan.php" class="btn btn-primary btn-sm px-3 shadow-sm fw-bold"><i
                            class="bi bi-plus-lg me-1"></i> Tambah Pengajuan</a>
                </div>

                <table id="tabelIzin" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>NO</th>
                            <th>
                                <div class="filter-container" data-col="1">
                                    NAMA LENGKAP <i class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card">
                                        <div class="filter-body p-2">
                                            <div class="filter-search-box mb-2"><input type="text"
                                                    class="form-control form-control-sm" placeholder="Cari nama...">
                                            </div>
                                            <div class="filter-list-container border p-1"
                                                style="max-height: 150px; overflow-y: auto;">
                                                <div class="filter-option fw-bold"><input type="checkbox"
                                                        class="select-all" checked> (All)</div>
                                                <div class="options-list"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th>JENIS</th>
                            <th>
                                <div class="filter-container" data-col="3">
                                    DIVISI <i class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card">
                                        <div class="filter-body p-2">
                                            <div class="filter-search-box mb-2"><input type="text"
                                                    class="form-control form-control-sm" placeholder="Cari divisi...">
                                            </div>
                                            <div class="filter-list-container border p-1"
                                                style="max-height: 150px; overflow-y: auto;">
                                                <div class="filter-option fw-bold"><input type="checkbox"
                                                        class="select-all" checked> (All)</div>
                                                <div class="options-list"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th>DURASI <input type="month" id="monthFilter" class="ms-1 border-0"
                                    style="width: 20px; background:transparent; cursor: pointer;"></th>
                            <th>KETERANGAN</th>
                            <th>DOKUMEN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)):
                            $jenis = $row['jenis_izin'];
                            $bg_class = ($jenis == 'Sakit') ? 'badge-sakit' : (($jenis == 'Cuti') ? 'badge-cuti' : 'badge-izin');
                            $tgl_mulai = ($row['tgl_mulai'] && $row['tgl_mulai'] != '0000-00-00') ? date('d/m/Y', strtotime($row['tgl_mulai'])) : '-';
                            $tgl_selesai = ($row['tgl_selesai'] && $row['tgl_selesai'] != '0000-00-00') ? date('d/m/Y', strtotime($row['tgl_selesai'])) : '-';
                            ?>
                            <tr>
                                <td></td>
                                <td class="fw-bold"><?= $row['nama_lengkap']; ?></td>
                                <td><span class="badge <?= $bg_class; ?> rounded-pill px-3"><?= $jenis; ?></span></td>
                                <td><?= $row['divisi']; ?></td>
                                <td>
                                    <small class="d-block fw-bold text-dark"><?= $tgl_mulai; ?></small>
                                    <small class="text-muted">s/d <?= $tgl_selesai; ?></small>
                                </td>
                                <td><small class="text-truncate d-inline-block"
                                        style="max-width: 150px;"><?= $row['keterangan']; ?></small></td>
                                <td>
                                    <?php if (!empty($row['lampiran'])): ?>
                                        <a href="../assets/dokumen/<?= $row['lampiran']; ?>" target="_blank"
                                            class="btn btn-outline-info btn-sm py-0 px-2">
                                            <i class="bi bi-file-earmark-text"></i> Buka
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#tabelIzin').DataTable({
                dom: 'rtp',
                pageLength: 10,
                columnDefs: [{ "searchable": false, "orderable": false, "targets": 0 }],
                order: [[4, 'desc']]
            });

            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            $('.filter-icon').on('click', function (e) {
                e.stopPropagation();
                let card = $(this).siblings('.excel-filter-card');
                $('.excel-filter-card').not(card).hide();
                card.toggle();
            });

            table.columns([1, 3]).every(function () {
                var column = this;
                var colIdx = column.index();
                var list = $(`.filter-container[data-col="${colIdx}"] .options-list`);
                column.data().unique().sort().each(function (d) {
                    if (d) list.append(`<div class="filter-option"><input type="checkbox" value="${d}" checked> ${d}</div>`);
                });
            });

            $(document).on('change', '.filter-option input, .select-all', function () {
                var container = $(this).closest('.filter-container');
                var colIdx = container.data('col');
                var selectAll = container.find('.select-all');
                if ($(this).hasClass('select-all')) {
                    container.find('.options-list input').prop('checked', selectAll.prop('checked'));
                } else {
                    selectAll.prop('checked', container.find('.options-list input:not(:checked)').length === 0);
                }
                var searchVals = container.find('.options-list input:checked').map(function () {
                    return $.fn.dataTable.util.escapeRegex($(this).val());
                }).get().join('|');
                table.column(colIdx).search(searchVals ? '^(' + searchVals + ')$' : '^$', true, false).draw();
                container.find('.filter-icon').toggleClass('active', !selectAll.prop('checked'));
            });

            $(document).click(function () { $('.excel-filter-card').hide(); });
            $('.excel-filter-card').click(function (e) { e.stopPropagation(); });
        });
    </script>
</body>

</html>