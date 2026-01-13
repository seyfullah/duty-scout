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
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 100%;
            max-width: 370px;
            padding: 2.2rem 1.7rem 1.7rem 1.7rem;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(60, 80, 180, 0.13);
            border: 1px solid #e0e7ff;
        }
        .login-box h4 {
            font-weight: 700;
            color: #3b4cca;
        }
        .btn-primary {
            background: #3b4cca;
            border-color: #3b4cca;
        }
        .btn-success {
            background: #22c55e;
            border-color: #22c55e;
        }
        .btn-link {
            color: #3b4cca;
        }
        .form-label {
            font-weight: 500;
        }
        .invalid-feedback {
            font-size: 0.97em;
        }
        ::placeholder {
            color: #b6b6b6 !important;
            opacity: 1;
        }
        .alert-danger {
            font-size: 1em;
            padding: 0.5em 1em;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <h4 class="mb-4 text-center">Giriş Yap</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center mb-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                        autofocus
                        placeholder="500 000 00 00" maxlength="13"
                        pattern="5[0-9]{2} [0-9]{3} [0-9]{2} [0-9]{2}">
                    <div id="phone-error" class="invalid-feedback" style="display:none;">Telefon numarası giriniz!</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2">Giriş</button>
            </form>
            <a href="https://www.pusulader.org.tr/yigitler/register.php" class="btn btn-success w-100 mb-2">Kayıt Ol</a>
            <a href="https://support.google.com/chrome/answer/15085120?hl=tr&co=GENIE.Platform%3DiOS&oco=0"
                target="_blank" rel="noopener"
                class="btn btn-link w-100 mb-1">Chrome'da web siteleri için kısayollar oluşturma</a>
        </div>
    </div>
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
        // Sadece Giriş butonuna basınca telefon zorunlu olsun
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneError = document.getElementById('phone-error');
            if (!phoneInput.value.trim()) {
                e.preventDefault();
                phoneInput.classList.add('is-invalid');
                phoneError.style.display = 'block';
                phoneInput.focus();
            } else {
                phoneInput.classList.remove('is-invalid');
                phoneError.style.display = 'none';
            }
        });
    </script>
</body>

</html>