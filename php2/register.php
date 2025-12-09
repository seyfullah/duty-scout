<?php
session_start();
require 'includes/db.php';

// Grupları çek
$stmt = $pdo->query("SELECT id, name FROM groups ORDER BY name");
$groups = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $group_id = $_POST['group_id'];

    // Basit kontrol
    if ($name && $phone && $password && $group_id) {
        // Grup dolu mu?
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE group_id = ?");
        $stmt->execute([$group_id]);
        $group_count = $stmt->fetchColumn();
        if ($group_count >= 4) {
            $error = "Bu grupta zaten 4 kişi var!";
        } else {
            // Telefon benzersiz mi?
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $error = "Bu telefon numarası zaten kayıtlı!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, phone, password, group_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $phone, $password, $group_id]);
                header('Location: login.php');
                exit;
            }
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
    <title>Kayıt Ol</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html { height: 100%; }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }
        .register-box {
            width: 100%;
            max-width: 370px;
            padding: 2rem 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="register-box">
        <h4 class="mb-4 text-center">Kayıt Ol</h4>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="name" class="form-label">Ad Soyad</label>
                <input type="text" class="form-control" id="name" name="name" required autofocus>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
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
            <button type="submit" class="btn btn-success w-100">Kayıt Ol</button>
        </form>
        <div class="mt-3 text-center">
            <a href="login.php" class="text-decoration-none">Giriş Yap</a>
        </div>
    </div>
</div>
</body>
</html>