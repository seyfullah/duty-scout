<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}
$success = '';
// STK ekle
if (isset($_POST['add_ngo'])) {
    $ngo_name = trim($_POST['ngo_name']);
    $responsible_id = isset($_POST['responsible_id']) && $_POST['responsible_id'] !== '' ? $_POST['responsible_id'] : null;
    if ($ngo_name) {
        $stmt = $pdo->prepare("INSERT INTO ngos (name, responsible_id) VALUES (?, ?)");
        $stmt->execute([$ngo_name, $responsible_id]);
        $success = 'STK eklendi!';
        header('Location: ngos.php');
        exit;
    }
}
// STK güncelle
if (isset($_POST['update_ngo'])) {
    $ngo_id = $_POST['ngo_id'];
    $ngo_name = trim($_POST['ngo_name']);
    $responsible_id = isset($_POST['responsible_id']) && $_POST['responsible_id'] !== '' ? $_POST['responsible_id'] : null;
    if ($ngo_id && $ngo_name) {
        $stmt = $pdo->prepare("UPDATE ngos SET name = ?, responsible_id = ? WHERE id = ?");
        $stmt->execute([$ngo_name, $responsible_id, $ngo_id]);
        $success = 'STK güncellendi!';
        header('Location: ngos.php');
        exit;
    }
}
// STK sil
if (isset($_POST['delete_ngo'])) {
    $ngo_id = $_POST['delete_ngo'];
    $stmt = $pdo->prepare("DELETE FROM ngos WHERE id = ?");
    $stmt->execute([$ngo_id]);
    $success = 'STK silindi!';
    header('Location: ngos.php');
    exit;
}
$ngos = $pdo->query("SELECT * FROM ngos ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>STK'lar</title>
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
        <?php $active_page = 'ngos'; include_once '../includes/header.php'; ?>
        <h4 class="mb-3 text-center">STK'lar</h4>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?= $success ?> </div>
        <?php endif; ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-5 mx-auto">
                <form method="post" autocomplete="off">
                    <div class="mb-2">
                        <input type="text" name="ngo_name" class="form-control" placeholder="Yeni STK Adı" required>
                    </div>
                    <button type="submit" name="add_ngo" class="btn btn-primary w-100">STK Ekle</button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>STK Adı</th>
                        <th>Sorumlu</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ngos as $n): ?>
                        <tr>
                            <form method="post" class="d-flex flex-wrap gap-2">
                                <td><input type="text" name="ngo_name" value="<?= htmlspecialchars($n['name']) ?>" class="form-control form-control-sm" required></td>
                                <td>
                                    <select name="responsible_id" class="form-select form-select-sm">
                                        <option value="">Sorumlu Yok</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= (isset($n['responsible_id']) && $n['responsible_id'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="ngo_id" value="<?= $n['id'] ?>">
                                    <button type="submit" name="update_ngo" class="btn btn-sm btn-primary">Düzenle</button>
                                    <button type="submit" name="delete_ngo" value="<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('STK silinsin mi?')">Sil</button>
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
