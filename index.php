<?php
session_start();
include 'config/koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['divisi']   = $data['divisi'];
        $_SESSION['role']     = $data['role'];

        header("Location: views/dashboard.php");
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Login - AnkaraOne</title>
    <style>
        body { 
            background-color: #fcfcfc; 
            font-family: 'Segoe UI', sans-serif;
        }
        .card-login {
            border: 1px solid #eee;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            margin-top: -50px; /* Membuat card agak naik ke tengah */
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.9rem;
            border: 1px solid #eee;
            background: #fdfdfd;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
            background: #fff;
        }
        .btn-login {
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            background-color: #333; /* Senada dengan Navbar */
            border: none;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #000;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="container">
        <div class="card card-login mx-auto p-3" style="max-width: 400px;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <img src="logo.PNG" alt="Logo" width="40" height="40" class="me-3 rounded-circle border shadow-sm">
                    <h4 class="fw-bold mb-0" style="letter-spacing: 1px; color: #333;">ANKARAONE</h4>
                </div>

                <p class="text-center text-muted small mb-4">Silakan masukkan akun internal Anda</p>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger py-2 small border-0 text-center" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control border-start-0" placeholder="Ketik username..." required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-1">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control border-start-0" placeholder="Ketik password..." required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-login w-100 text-white">
                        MASUK <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <small class="text-muted" style="font-size: 0.7rem;">&copy; 2025 KBRI Ankara | Internal Only</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>