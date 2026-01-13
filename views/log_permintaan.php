<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
include '../config/koneksi.php';

// Proteksi akses khusus Kanselerai atau Admin
if (!isset($_SESSION['username']) || ($_SESSION['divisi'] != 'PF Kanselerai' && $_SESSION['divisi'] != 'Master Admin')) {
    echo "<script>alert('Akses Khusus Kanselerai!'); window.location='dashboard.php';</script>";
    exit();
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
$role_user = $_SESSION['divisi'];

// Hak akses Admin
$isAdmin = ($role_user == 'PF Kanselerai' || $role_user == 'Master Admin');

$query = "SELECT l.*, u.nama_lengkap FROM log_permintaan l JOIN users u ON l.id_user = u.id_user ORDER BY l.tgl_input DESC";
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
    <title>Log Permintaan TU BMN - AnkaraOne</title>
    <style>
        /* DISAMAKAN PERSIS DENGAN TU.PHP */
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

        /* SECONDARY NAVBAR (KHUSUS TATA USAHA) */
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

        /* WARNA DROPDOWN DINAMIS */
        select.prog-done {
            background-color: #198754 !important;
            color: white !important;
            font-weight: bold;
        }

        select.prog-hold {
            background-color: #fd7e14 !important;
            color: white !important;
        }

        select.prog-pending {
            background-color: #dc3545 !important;
            color: white !important;
        }

        select.urg-urgent {
            background-color: #8b0000 !important;
            color: white !important;
            font-weight: bold;
        }

        select.urg-medium {
            background-color: #fff3cd !important;
            color: #856404 !important;
        }

        select.urg-nonurgent {
            background-color: #e2e3e5 !important;
            color: #41464b !important;
        }

        /* Filter Popover Styling */
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
            width: 200px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 10px;
            top: 25px;
            left: 0;
            text-align: left;
            z-index: 1050;
        }

        .options-list {
            max-height: 150px;
            overflow-y: auto;
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .filter-option {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            padding: 3px 0;
            color: #333;
        }

        .filter-option input {
            margin-right: 8px;
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
                        <div class="fw-bold" style="font-size: 0.85rem; line-height: 1.2;">
                            <?php echo $nama_user; ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;">
                            <?php echo $role_user; ?>
                        </div>
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
            <a class="nav-link" href="tu.php"><i class="bi bi-pie-chart-fill me-2"></i>HOME TU</a>
            <a class="nav-link active" href="log_permintaan.php"><i class="bi bi-clipboard-data me-2"></i>LOG
                PERMINTAAN</a>
            <a class="nav-link" href="form_permintaan.php"><i class="bi bi-box-seam me-2"></i>LOG PENDATAAN
                INVENTARIS</a>
            <a class="nav-link" href="#"><i class="bi bi-pencil-square me-2"></i>LOG PENDATAAN</a>
        </div>
    </div>

    <div class="container-fluid px-5">
        <main>
            <div class="table-card border-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item small"><a href="dashboard.php"
                                class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item small"><a href="tu.php" class="text-decoration-none text-muted">Tata
                                Usaha</a></li>
                        <li class="breadcrumb-item active small text-primary fw-bold" aria-current="page">Log Permintaan
                            TU BMN</li>
                    </ol>
                </nav>

                <h4 class="fw-bold mb-4 text-dark">Log Permintaan Masuk</h4>

                <table id="tabelLog" class="table table-bordered align-middle w-100">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Tanggal <div class="filter-container" data-col="0"><i
                                        class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card"><b>Pilih Tanggal</b>
                                        <div class="options-list"></div>
                                    </div>
                                </div>
                            </th>
                            <th>Request</th>
                            <th>From <div class="filter-container" data-col="2"><i
                                        class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card"><b>Filter Nama</b>
                                        <div class="options-list"></div>
                                    </div>
                                </div>
                            </th>
                            <th>Deadline</th>
                            <th>Lokasi</th>
                            <th>Progress <div class="filter-container" data-col="5"><i
                                        class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card"><b>Saring Status</b>
                                        <div class="options-list"></div>
                                    </div>
                                </div>
                            </th>
                            <th>PIC <div class="filter-container" data-col="6"><i
                                        class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card"><b>Personil</b>
                                        <div class="options-list"></div>
                                    </div>
                                </div>
                            </th>
                            <th>Urgency <div class="filter-container" data-col="7"><i
                                        class="bi bi-funnel-fill filter-icon"></i>
                                    <div class="excel-filter-card"><b>Urgensi</b>
                                        <div class="options-list"></div>
                                    </div>
                                </div>
                            </th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)):
                            $progClass = "prog-" . strtolower(str_replace(' ', '', $row['progress']));
                            $urgClass = "urg-" . strtolower(str_replace([' ', '-'], '', $row['urgency_level']));
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?= ($row['tgl_input'] != '0000-00-00' && !empty($row['tgl_input'])) ? date('d M Y', strtotime($row['tgl_input'])) : '-'; ?>
                                </td>
                                <td><?= $row['nama_request']; ?></td>
                                <td class="fw-bold"><?= $row['nama_lengkap']; ?></td>
                                <td class="text-center small"><?= $row['deadline']; ?></td>
                                <td>
                                    <select onchange="updateLog(<?= $row['id_log']; ?>, 'lokasi_gedung', this)"
                                        class="form-select form-select-sm" style="font-size: 0.75rem;">
                                        <option value="ICC" <?= $row['lokasi_gedung'] == 'ICC' ? 'selected' : ''; ?>>ICC
                                        </option>
                                        <option value="Kendis" <?= $row['lokasi_gedung'] == 'Kendis' ? 'selected' : ''; ?>>
                                            Kendis
                                        </option>
                                        <option value="Kantor Baru" <?= $row['lokasi_gedung'] == 'Kantor Baru' ? 'selected' : ''; ?>>Kantor Baru</option>
                                        <option value="Lainnya" <?= $row['lokasi_gedung'] == 'Lainnya' ? 'selected' : ''; ?>>
                                            Lainnya</option>
                                    </select>
                                </td>
                                <td>
                                    <select onchange="updateLog(<?= $row['id_log']; ?>, 'progress', this)"
                                        class="form-select form-select-sm <?= $progClass; ?>" style="font-size: 0.75rem;">
                                        <option value="Done" <?= $row['progress'] == 'Done' ? 'selected' : ''; ?>>Done</option>
                                        <option value="Hold" <?= $row['progress'] == 'Hold' ? 'selected' : ''; ?>>Hold</option>
                                        <option value="Pending" <?= $row['progress'] == 'Pending' ? 'selected' : ''; ?>>Pending
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <select onchange="updateLog(<?= $row['id_log']; ?>, 'pic', this)"
                                        class="form-select form-select-sm" style="font-size: 0.75rem;">
                                        <option value="Gilang" <?= $row['pic'] == 'Gilang' ? 'selected' : ''; ?>>Gilang
                                        </option>
                                        <option value="Mas Ogi" <?= $row['pic'] == 'Mas Ogi' ? 'selected' : ''; ?>>Mas Ogi
                                        </option>
                                        <option value="Ghiyats" <?= $row['pic'] == 'Ghiyats' ? 'selected' : ''; ?>>Ghiyats
                                        </option>
                                        <option value="Pak Nur Zaman" <?= $row['pic'] == 'Pak Nur Zaman' ? 'selected' : ''; ?>>
                                            Pak
                                            Nur Zaman</option>
                                        <option value="Mas Humam" <?= $row['pic'] == 'Mas Humam' ? 'selected' : ''; ?>>Mas
                                            Humam
                                        </option>
                                        <option value="Bu Nisa" <?= $row['pic'] == 'Bu Nisa' ? 'selected' : ''; ?>>Bu Nisa
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <select onchange="updateLog(<?= $row['id_log']; ?>, 'urgency_level', this)"
                                        class="form-select form-select-sm <?= $urgClass; ?>" style="font-size: 0.75rem;">
                                        <option value="Urgent" <?= $row['urgency_level'] == 'Urgent' ? 'selected' : ''; ?>>
                                            Urgent
                                        </option>
                                        <option value="Medium" <?= $row['urgency_level'] == 'Medium' ? 'selected' : ''; ?>>
                                            Medium
                                        </option>
                                        <option value="Non-Urgent" <?= $row['urgency_level'] == 'Non-Urgent' ? 'selected' : ''; ?>>
                                            Non-Urgent</option>
                                    </select>
                                </td>
                                <td><small><?= $row['keterangan']; ?></small></td>
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
            var table = $('#tabelLog').DataTable({
                "dom": "rtp",
                "pageLength": 25,
                "order": [[0, "desc"]]
            });

            $('.filter-icon').on('click', function (e) {
                e.stopPropagation();
                let card = $(this).siblings('.excel-filter-card');
                $('.excel-filter-card').not(card).hide();
                card.toggle();
            });

            table.columns([0, 2, 5, 6, 7]).every(function () {
                var column = this;
                var colIdx = column.index();
                var list = $(`.filter-container[data-col="${colIdx}"] .options-list`);
                list.empty().append('<div class="filter-option fw-bold"><input type="checkbox" class="select-all" checked> (All)</div>');
                column.data().unique().sort().each(function (d) {
                    if (d) {
                        let cleanData = d.replace(/<\/?[^>]+(>|$)/g, "");
                        list.append(`<div class="filter-option"><input type="checkbox" value="${cleanData}" checked> ${cleanData}</div>`);
                    }
                });
            });

            $(document).on('change', '.filter-option input', function () {
                var container = $(this).closest('.filter-container');
                var colIdx = container.data('col');
                var selectAll = container.find('.select-all');
                if ($(this).hasClass('select-all')) {
                    container.find('.options-list input').prop('checked', selectAll.prop('checked'));
                } else {
                    selectAll.prop('checked', container.find('.options-list input:not(:checked)').length === 0);
                }
                var searchVals = container.find('.options-list input:checked:not(.select-all)').map(function () {
                    return $.fn.dataTable.util.escapeRegex($(this).val());
                }).get().join('|');
                table.column(colIdx).search(searchVals ? '^(' + searchVals + ')$' : '^$', true, false).draw();
                container.find('.filter-icon').toggleClass('active', !selectAll.prop('checked'));
            });

            $(document).click(function () { $('.excel-filter-card').hide(); });
            $('.excel-filter-card').click(function (e) { e.stopPropagation(); });
        });

        function updateLog(id, kolom, element) {
            let nilai = (typeof element === 'string') ? element : element.value;
            if (typeof element !== 'string') {
                if (kolom === 'progress') {
                    element.classList.remove('prog-done', 'prog-hold', 'prog-pending');
                    element.classList.add('prog-' + nilai.toLowerCase().replace(/\s/g, ''));
                } else if (kolom === 'urgency_level') {
                    element.classList.remove('urg-urgent', 'urg-medium', 'urg-nonurgent');
                    element.classList.add('urg-' + nilai.toLowerCase().replace(/-/g, '').replace(/\s/g, ''));
                }
            }
            $.post('update_log_aksi.php', { id: id, kolom: kolom, nilai: nilai }, function (res) {
                console.log("Database Updated: " + res);
            });
        }
    </script>
</body>

</html>