<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}
$success = '';
// Grup ekle
if (isset($_POST['add_group'])) {
    $group_name = trim($_POST['group_name']);
    if ($group_name) {
        $stmt = $pdo->prepare("INSERT INTO groups (name) VALUES (?)");
        $stmt->execute([$group_name]);
        $success = 'Grup eklendi!';
        header('Location: groups.php');
        exit;
    }
}
// Grup güncelle
if (isset($_POST['update_group'])) {
    $group_id = $_POST['group_id'];
    $group_name = trim($_POST['group_name']);
    if ($group_id && $group_name) {
        $stmt = $pdo->prepare("UPDATE groups SET name = ? WHERE id = ?");
        $stmt->execute([$group_name, $group_id]);
        $success = 'Grup güncellendi!';
        header('Location: groups.php');
        exit;
    }
}
// Grup sil
if (isset($_POST['delete_group'])) {
    $group_id = $_POST['delete_group'];
    // Önce o gruptaki kullanıcıların group_id'sini NULL yap
    $stmt = $pdo->prepare("UPDATE users SET group_id = NULL WHERE group_id = ?");
    $stmt->execute([$group_id]);
    $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    $success = 'Grup silindi!';
    header('Location: groups.php');
    exit;
}
$groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Gruplar</title>
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
        <?php $active_page = 'groups'; include_once '../includes/header.php'; ?>
        <h4 class="mb-3 text-center">Gruplar</h4>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?= $success ?> </div>
        <?php endif; ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-5 mx-auto">
                <form method="post" autocomplete="off">
                    <div class="mb-2">
                        <input type="text" name="group_name" class="form-control" placeholder="Yeni Grup Adı" required>
                    </div>
                    <button type="submit" name="add_group" class="btn btn-primary w-100">Grup Ekle</button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Grup Adı</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $g): ?>
                        <tr>
                            <form method="post" class="d-flex flex-wrap gap-2">
                                <td><input type="text" name="group_name" value="<?= htmlspecialchars($g['name']) ?>" class="form-control form-control-sm" required></td>
                                <td>
                                    <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                                    <button type="submit" name="update_group" class="btn btn-sm btn-primary">Düzenle</button>
                                    <button type="submit" name="delete_group" value="<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Grup silinsin mi?')">Sil</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>