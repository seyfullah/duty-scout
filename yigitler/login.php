<?php
session_start();
require 'includes/db.php';

// Cookie ile otomatik giriş
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $user_id = $_COOKIE['remember_user'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = !empty($user['is_admin']);
        header('Location: submit_score.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = str_replace(' ', '', $_POST['phone']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = !empty($user['is_admin']);
        setcookie('remember_user', $user['id'], time() + 60 * 60 * 24 * 30, '/'); // 30 gün hatırla
        header('Location: submit_score.php');
        exit;
    } else {
        $error = "Telefon yanlış!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body,
        html {
            height: 100%;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .login-box {
            width: 100%;
            max-width: 350px;
            padding: 2rem 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        }

        ::placeholder {
            color: #bbb !important;
            opacity: 1;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <h4 class="mb-4 text-center">Giriş Yap</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                        required autofocus
                        placeholder="500 000 00 00" maxlength="13"
                        pattern="5[0-9]{2} [0-9]{3} [0-9]{2} [0-9]{2}">
                </div>
                <button type="submit" class="btn btn-primary w-100">Giriş</button>
            </form>
            <script>
                // Telefon numarası girerken otomatik olarak 5xx xxx xx xx formatına dönüştür
                const phoneInput = document.getElementById('phone');
                phoneInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/[^0-9]/g, '');
                    let formatted = '';
                    if (value.length > 0) {
                        formatted = value[0];
                        if (value.length > 1) formatted += value.slice(1, 3);
                        if (value.length > 3) formatted += ' ' + value.slice(3, 6);
                        if (value.length > 6) formatted += ' ' + value.slice(6, 8);
                        if (value.length > 8) formatted += ' ' + value.slice(8, 10);
                    }
                    this.value = formatted;
                });
            </script>
            </form>
            <a href="https://support.google.com/chrome/answer/15085120?hl=tr&co=GENIE.Platform%3DiOS&oco=0"
                target="_blank" rel="noopener"
                style="color:#007bff;">Chrome'da web siteleri için kısayollar oluşturma</a>
        </div>
    </div>
</body>

</html>