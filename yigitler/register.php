<?php
session_start();
require 'includes/db.php';
// Grupları çek
$stmt = $pdo->query("SELECT id, name FROM groups ORDER BY name");
$groups = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = str_replace(' ', '', trim($_POST['phone']));
    $group_id = $_POST['group_id'];
    if ($name && $phone && $group_id) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = "Bu telefon numarası zaten kayıtlı!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, phone, group_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $group_id]);
            header('Location: login.php');
            exit;
        }
    } else {
        $error = "Tüm alanları doldurun!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Kaydet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-box {
            width: 100%;
            max-width: 370px;
            padding: 2.2rem 1.7rem 1.7rem 1.7rem;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(60, 80, 180, 0.13);
            border: 1px solid #e0e7ff;
        }
        .register-box h4 {
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
<div class="register-container">
    <div class="register-box">
        <h4 class="mb-4 text-center">Kullanıcı Kaydet</h4>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center mb-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="name" class="form-label">Ad Soyad</label>
                <input type="text" class="form-control" id="name" name="name" required autofocus>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input type="text" class="form-control" id="phone" name="phone" autofocus placeholder="500 000 00 00" maxlength="13" pattern="5[0-9]{2} [0-9]{3} [0-9]{2} [0-9]{2}">
                <div id="phone-error" class="invalid-feedback" style="display:none;">Telefon numarası giriniz!</div>
            </div>
            <div class="mb-3">
                <label for="group_id" class="form-label">Grup</label>
                <select class="form-select" id="group_id" name="group_id" required>
                    <option value="">Grup Seçiniz</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Kaydet</button>
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
                    // Sadece Kaydet butonuna basınca telefon zorunlu olsun
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
        </form>
        <a href="login.php" class="btn btn-link w-100 mb-1">Giriş Yap</a>
    </div>
</div>
</body>
</html>