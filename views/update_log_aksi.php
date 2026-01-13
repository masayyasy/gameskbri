<?php
include '../config/koneksi.php';

if (isset($_POST['id']) && isset($_POST['kolom']) && isset($_POST['nilai'])) {
    $id    = mysqli_real_escape_string($conn, $_POST['id']);
    $kolom = mysqli_real_escape_string($conn, $_POST['kolom']);
    $nilai = mysqli_real_escape_string($conn, $_POST['nilai']);

    // Update permanen ke database agar tidak reset saat refresh
    $query = "UPDATE log_permintaan SET $kolom = '$nilai' WHERE id_log = '$id'";
    mysqli_query($conn, $query);
}
?>