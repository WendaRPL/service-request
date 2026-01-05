<?php
require_once 'direct/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
        SELECT id, username, password, role, toko_id
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id']  = $user['role'];
            $_SESSION['toko_id']  = $user['toko_id'];

            header('Location: home.php');
            exit;
        } else {
            $error_msg = "Password salah!";
        }
    } else {
        $error_msg = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Service Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dist/css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="header-section">
            <h1 class="title">Service Request</h1>
            <p class="subtitle">Login menggunakan akun database</p>
        </div>

        <div class="login-card">
            <div class="wave-decoration"></div>
            <div class="success-animation" id="successAnimation">
                <div class="success-icon"><i data-feather="check"></i></div>
                <h3 style="color: white; font-size: 1.25rem; font-weight: 600;">Login Berhasil!</h3>
            </div>
            
            <div class="card-content">
                <h2 class="welcome-text">Selamat Datang</h2>

                <?php if ($error_msg): ?>
                    <div style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.875rem; border: 1px solid #fecaca;">
                        <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="index.php" class="login-form-content">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-container">
                            <div class="input-icon"><i data-feather="user" class="icon-gray"></i></div>
                            <input type="text" name="username" id="username" required class="form-input" placeholder="Username" value="<?= htmlspecialchars($username ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-container">
                            <div class="input-icon"><i data-feather="lock" class="icon-gray"></i></div>
                            <input type="password" name="password" id="password" required class="form-input" placeholder="Password">
                        </div>
                    </div>

                    <button type="submit" id="loginBtn" class="submit-btn">
                        <span id="loginText">Masuk ke Sistem</span>
                        <div id="loginSpinner" class="spinner-container hidden"><div class="loading-spinner"></div></div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const loginText = document.getElementById('loginText');
        const loginSpinner = document.getElementById('loginSpinner');
        const successAnimation = document.getElementById('successAnimation');

        loginForm.addEventListener('submit', function(e) {
            // Cek apakah ini submit pertama (untuk animasi)
            if (!loginForm.dataset.validated) {
                e.preventDefault();
                
                // Animasi Loading
                loginText.textContent = 'Memverifikasi...';
                loginSpinner.classList.remove('hidden');
                loginBtn.disabled = true;

                // Simulasi Delay biar animasi keliatan
                setTimeout(() => {
                    // Tandai form sudah divalidasi dan submit beneran
                    loginForm.dataset.validated = "true";
                    loginForm.submit(); 
                }, 1000);
            }
        });
    </script>
</body>
</html>