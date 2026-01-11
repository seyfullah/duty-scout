<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}
$success = '';
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
$groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll();
// Kullanıcıyı gruba ata
if (isset($_POST['assign_user_to_group'])) {
    $user_id = $_POST['assign_user_id'];
    $group_id = $_POST['assign_group_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE group_id = ? AND id != (SELECT captain_id FROM groups WHERE id = ?)");
    $stmt->execute([$group_id, $group_id]);
    $count = $stmt->fetchColumn();
    if ($count < 4) {
        $stmt = $pdo->prepare("UPDATE users SET group_id = ? WHERE id = ?");
        $stmt->execute([$group_id, $user_id]);
        $success = 'Kullanıcı gruba atandı!';
    } else {
        $success = 'Bu grupta zaten 4 üye var!';
    }
    header('Location: group_users.php');
    exit;
}
// Grup sorumlusu ata
if (isset($_POST['make_captain'])) {
    $group_id = $_POST['captain_group_id'];
    $user_id = $_POST['captain_user_id'];
    if ($group_id && $user_id) {
        $stmt = $pdo->prepare("UPDATE groups SET captain_id = ? WHERE id = ?");
        $stmt->execute([$user_id, $group_id]);
        $success = 'Grup sorumlusu atandı!';
        header('Location: group_users.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Grup Kullanıcıları</title>
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
        <?php $active_page = 'group_users'; include_once '../includes/header.php'; ?>
        <h4 class="mb-3 text-center">Grup Kullanıcıları</h4>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"> <?= $success ?> </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-12 col-md-10 col-lg-8 mx-auto">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Grup</th>
                                <th>Üyeler</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $g): ?>
                                <tr>
                                    <td><?= htmlspecialchars($g['name']) ?></td>
                                    <td>
                                        <?php
                                        $group_users = array_filter($users, fn($u) => $u['group_id'] == $g['id']);
                                        foreach ($group_users as $u) {
                                        ?>
                                            <div class="d-flex align-items-center mb-1" style="gap:6px;">
                                                <span><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars(format_phone($u['phone'])) ?>)</span>
                                                <?php if ($g['captain_id'] == $u['id']): ?>
                                                    <span class="badge bg-warning text-dark">Sorumlu</span>
                                                <?php else: ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="captain_group_id" value="<?= $g['id'] ?>">
                                                        <input type="hidden" name="captain_user_id" value="<?= $u['id'] ?>">
                                                        <button type="submit" name="make_captain" class="btn btn-sm btn-outline-warning py-0 px-1">Sorumlu Yap</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td><span class="badge bg-secondary">Toplam: <?= count($group_users) ?>/5</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 col-md-10 col-lg-8 mx-auto">
                <h5>Grupta Olmayanlar</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Telefon</th>
                                <th>Gruba Ata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $ungrouped = array_filter($users, fn($u) => !$u['group_id']);
                            foreach ($ungrouped as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars(format_phone($u['phone'])) ?></td>
                                    <td>
                                        <form method="post" class="d-flex flex-wrap gap-2">
                                            <input type="hidden" name="assign_user_id" value="<?= $u['id'] ?>">
                                            <select name="assign_group_id" class="form-select form-select-sm" required>
                                                <option value="">Grup Seç</option>
                                                <?php foreach ($groups as $g):
                                                    $group_users = array_filter($users, fn($gu) => $gu['group_id'] == $g['id']);
                                                    if (count($group_users) < 5): ?>
                                                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                                                <?php endif;
                                                endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_user_to_group" class="btn btn-sm btn-primary">Ata</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>