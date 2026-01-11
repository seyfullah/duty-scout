<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}
$success = '';
// Kullanıcı ekle
if (isset($_POST['add_user'])) {
    $name = trim($_POST['user_name']);
    $phone = str_replace(' ', '', trim($_POST['user_phone']));
    if ($name && $phone) {
        $stmt = $pdo->prepare("INSERT INTO users (name, phone) VALUES (?, ?)");
        $stmt->execute([$name, $phone]);
        $success = 'Kullanıcı eklendi!';
        header('Location: .//users.php');
        exit;
    }
}
// Kullanıcı güncelle
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = trim($_POST['user_name']);
    $phone = str_replace(' ', '', trim($_POST['user_phone']));
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $user_id]);
    $success = 'Kullanıcı güncellendi!';
    header('Location: users.php');
    exit;
}
// Kullanıcı sil
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['delete_user'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $success = 'Kullanıcı silindi!';
    header('Location: ../users.php');
    exit;
}
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcılar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media (max-width: 600px) {
            .container, .container-fluid {
                padding-left: 4px !important;
                padding-right: 4px !important;
            }
            .table td, .table th {
                font-size: 0.97em;
                padding: 0.35rem 0.2rem;
            }
            input.form-control, input.form-control-sm {
                font-size: 1em;
                padding: 0.3rem 0.5rem;
                min-width: 80px !important;
                max-width: 100%;
            }
            .btn, .btn-sm {
                font-size: 1em;
                padding: 0.35rem 0.7rem;
            }
            h4.mb-3 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-2">
        <?php $active_page = 'users'; include_once '../includes/header.php'; ?>
        <h4 class="mb-3 text-center">Kullanıcılar</h4>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?= $success ?> </div>
        <?php endif; ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-5 mx-auto">
                <form method="post" autocomplete="off">
                    <div class="mb-2">
                        <input type="text" name="user_name" class="form-control" placeholder="Ad Soyad" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="user_phone" class="form-control phone-mask" placeholder="555 111 22 33" maxlength="13" pattern="5[0-9]{2} [0-9]{3} [0-9]{2} [0-9]{2}" required>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-success w-100">Kullanıcı Ekle</button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Telefon</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <form method="post" class="d-flex flex-wrap gap-2">
                                <td><input type="text" name="user_name" value="<?= htmlspecialchars($u['name']) ?>" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="user_phone" value="<?= htmlspecialchars(format_phone($u['phone'])) ?>" class="form-control form-control-sm phone-mask" required></td>
                                <td>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" name="update_user" class="btn btn-sm btn-primary">Düzenle</button>
                                    <button type="submit" name="delete_user" value="<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kullanıcı silinsin mi?')">Sil</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var phoneInputs = document.querySelectorAll('.phone-mask');
            phoneInputs.forEach(function(phoneInput) {
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
            });
        });
    </script>
</body>

</html>