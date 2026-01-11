<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}
$success = '';
// Okul ekle
if (isset($_POST['add_school'])) {
    $school_name = trim($_POST['school_name']);
    $ngo_id = isset($_POST['ngo_id']) && $_POST['ngo_id'] !== '' ? $_POST['ngo_id'] : null;
    $responsible_id = isset($_POST['responsible_id']) && $_POST['responsible_id'] !== '' ? $_POST['responsible_id'] : null;
    if ($school_name) {
        $stmt = $pdo->prepare("INSERT INTO schools (name, ngo_id, responsible_id) VALUES (?, ?, ?)");
        $stmt->execute([$school_name, $ngo_id, $responsible_id]);
        $success = 'Okul eklendi!';
        header('Location: schools.php');
        exit;
    }
}
// Okul güncelle
if (isset($_POST['update_school'])) {
    $school_id = $_POST['school_id'];
    $school_name = trim($_POST['school_name']);
    $ngo_id = isset($_POST['ngo_id']) && $_POST['ngo_id'] !== '' ? $_POST['ngo_id'] : null;
    $responsible_id = isset($_POST['responsible_id']) && $_POST['responsible_id'] !== '' ? $_POST['responsible_id'] : null;
    if ($school_id && $school_name) {
        $stmt = $pdo->prepare("UPDATE schools SET name = ?, ngo_id = ?, responsible_id = ? WHERE id = ?");
        $stmt->execute([$school_name, $ngo_id, $responsible_id, $school_id]);
        $success = 'Okul güncellendi!';
        header('Location: schools.php');
        exit;
    }
}
// Okul sil
if (isset($_POST['delete_school'])) {
    $school_id = $_POST['delete_school'];
    $stmt = $pdo->prepare("DELETE FROM schools WHERE id = ?");
    $stmt->execute([$school_id]);
    $success = 'Okul silindi!';
    header('Location: schools.php');
    exit;
}
$schools = $pdo->query("SELECT * FROM schools ORDER BY name")->fetchAll();
$ngos = $pdo->query("SELECT * FROM ngos ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Okullar</title>
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
        <?php $active_page = 'schools'; include_once '../includes/header.php'; ?>
        <h4 class="mb-3 text-center">Okullar</h4>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?= $success ?> </div>
        <?php endif; ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-5 mx-auto">
                <form method="post" autocomplete="off">
                    <div class="mb-2">
                        <input type="text" name="school_name" class="form-control" placeholder="Yeni Okul Adı" required>
                    </div>
                    <button type="submit" name="add_school" class="btn btn-primary w-100">Okul Ekle</button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Okul Adı</th>
                        <th>Bağlı STK</th>
                        <th>Sorumlu</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schools as $s): ?>
                        <tr>
                            <form method="post" class="d-flex flex-wrap gap-2">
                                <td><input type="text" name="school_name" value="<?= htmlspecialchars($s['name']) ?>" class="form-control form-control-sm" required></td>
                                <td>
                                    <select name="ngo_id" class="form-select form-select-sm">
                                        <option value="">STK Yok</option>
                                        <?php foreach ($ngos as $ngo): ?>
                                            <option value="<?= $ngo['id'] ?>" <?= (isset($s['ngo_id']) && $s['ngo_id'] == $ngo['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ngo['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="responsible_id" class="form-select form-select-sm">
                                        <option value="">Sorumlu Yok</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= (isset($s['responsible_id']) && $s['responsible_id'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="school_id" value="<?= $s['id'] ?>">
                                    <button type="submit" name="update_school" class="btn btn-sm btn-primary">Düzenle</button>
                                    <button type="submit" name="delete_school" value="<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Okul silinsin mi?')">Sil</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
