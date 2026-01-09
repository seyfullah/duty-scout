<?php
// Yönetici kullanıcı oluşturma ve grup yönetimi sayfası
session_start();
require 'includes/db.php';

// Sadece admin erişebilir
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Grup ekle
if (isset($_POST['add_group'])) {
    $group_name = trim($_POST['group_name']);
    $captain_name = trim($_POST['captain_name']);
    $captain_phone = trim($_POST['captain_phone']);
    if ($group_name && $captain_name && $captain_phone) {
        $stmt = $pdo->prepare("INSERT INTO users (name, phone) VALUES (?, ?)");
        $stmt->execute([$captain_name, $captain_phone]);
        $captain_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO groups (name, captain_id) VALUES (?, ?)");
        $stmt->execute([$group_name, $captain_id]);
        $success = 'Grup ve sorumlu eklendi!';
        header('Location: users_admin.php'); exit;
    }
}
// Grup sil
if (isset($_POST['delete_group'])) {
    $group_id = $_POST['delete_group'];
    $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    $success = 'Grup silindi!';
}
// Grup güncelle
if (isset($_POST['update_group'])) {
    $group_id = $_POST['group_id'];
    $group_name = trim($_POST['group_name']);
    $captain_id = $_POST['captain_id'] ?? null;
    $captain_name = trim($_POST['captain_name']);
    $captain_phone = trim($_POST['captain_phone']);
    // Sorumlu bilgisini güncelle
    if ($captain_id && $captain_name && $captain_phone) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$captain_name, $captain_phone, $captain_id]);
    }
    // Grup bilgisini güncelle
    $stmt = $pdo->prepare("UPDATE groups SET name = ?, captain_id = ? WHERE id = ?");
    $stmt->execute([$group_name, $captain_id, $group_id]);
    $success = 'Grup ve sorumlu güncellendi!';
    header('Location: users_admin.php'); exit;
}
// Kullanıcıları ve grupları çek
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
$groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll();

// Kullanıcı ekle
if (isset($_POST['add_user'])) {
    $name = trim($_POST['user_name']);
    $phone = trim($_POST['user_phone']);
    $group_id = $_POST['user_group_id'] ?: null;
    if ($name && $phone) {
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, group_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $group_id]);
        $success = 'Kullanıcı eklendi!';
        header('Location: users_admin.php'); exit;
    }
}

// Grupta olmayan kullanıcıyı gruba ata
if (isset($_POST['assign_to_group'])) {
    $user_id = $_POST['assign_user_id'];
    $group_id = $_POST['assign_group_id'];
    // Grupta sorumlu hariç 4'ten fazla üye olamaz
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
    header('Location: users_admin.php'); exit;
}
// Kullanıcı güncelle
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = trim($_POST['user_name']);
    $phone = trim($_POST['user_phone']);
    $group_id = $_POST['user_group_id'] ?: null;
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, group_id = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $group_id, $user_id]);
    $success = 'Kullanıcı güncellendi!';
    header('Location: users_admin.php'); exit;
}
// Kullanıcı sil
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['delete_user'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $success = 'Kullanıcı silindi!';
    header('Location: users_admin.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetici Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive { overflow-x: auto; }
        @media (max-width: 600px) {
            .table td, .table th { font-size: 0.95em; padding: 0.3rem 0.2rem; }
            .ps-5 { padding-left: 0.7rem !important; }
            .d-flex.gap-2 { gap: 0.3rem !important; }
            input.form-control {
                font-size: 0.97em;
                padding: 0.25rem 0.4rem;
                min-width: 80px !important;
                max-width: 100%;
            }
            .container { padding-left: 2px !important; padding-right: 2px !important; }
            h4.mb-3 { font-size: 1.1rem; }
            .btn-sm { font-size: 0.95em; padding: 0.25rem 0.5rem; }
        }
        /* Mobilde input ve butonlar tam genişlikte olsun */
        @media (max-width: 600px) {
            .col-auto, .form-control, .btn, .table-responsive, .table {
                width: 100% !important;
                min-width: unset !important;
                max-width: unset !important;
            }
            .row.g-2.align-items-end > .col-auto {
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body class="bg-light">
<?php $active_page = 'users_admin'; include 'includes/header.php'; ?>
<div class="container py-2">
        <h4 class="mb-3 text-center">Grup ve Kullanıcı Yönetimi</h4>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"> <?= $success ?> </div>
    <?php endif; ?>
    <form method="post" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <input type="text" name="group_name" class="form-control" placeholder="Grup adı" required>
            </div>
            <div class="col-auto">
                <input type="text" name="captain_name" class="form-control" placeholder="Sorumlu Ad Soyad" required>
            </div>
            <div class="col-auto">
                <input type="text" name="captain_phone" class="form-control" placeholder="Telefon" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="add_group" class="btn btn-primary">Grup Ekle</button>
            </div>
        </div>
    </form>
    <div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead><tr><th>Grup</th><th>Kişi</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($groups as $g): ?>
            <tr>
                <form method="post">
                    <td><input type="text" name="group_name" value="<?= htmlspecialchars($g['name']) ?>" class="form-control" required></td>
                    <td>
                        <?php
                        $captain = null;
                        foreach ($users as $u) {
                            if ($u['id'] == $g['captain_id']) { $captain = $u; break; }
                        }
                        ?>
                        <input type="text" name="captain_name" value="<?= $captain ? htmlspecialchars($captain['name']) : '' ?>" class="form-control d-inline w-auto" style="min-width:120px;" placeholder="Sorumlu Ad Soyad" required>
                        <input type="text" name="captain_phone" value="<?= $captain ? htmlspecialchars($captain['phone']) : '' ?>" class="form-control d-inline w-auto" style="min-width:120px;" placeholder="Telefon" required>
                        <input type="hidden" name="captain_id" value="<?= $captain ? $captain['id'] : '' ?>">
                        <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                    </td>
                    <td>
                        <button type="submit" name="update_group" class="btn btn-success btn-sm">Güncelle</button>
                        <button type="submit" name="delete_group" value="<?= $g['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Grup silinsin mi?')">Sil</button>
                    </td>
                </form>
            </tr>
            <!-- Grup üyeleri -->
            <?php foreach ($users as $u): if ($u['group_id'] == $g['id'] && $u['id'] != $g['captain_id']): ?>
            <tr>
                <form method="post">
                    <td></td>
                    <td>
                        <input type="text" name="user_name" value="<?= htmlspecialchars($u['name']) ?>" class="form-control d-inline w-auto" style="min-width:120px;">
                        <input type="text" name="user_phone" value="<?= htmlspecialchars($u['phone']) ?>" class="form-control d-inline w-auto" style="min-width:120px;">
                        <input type="hidden" name="user_group_id" value="<?= $g['id'] ?>">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    </td>
                    <td>
                        <button type="submit" name="update_user" class="btn btn-sm btn-success">Kaydet</button>
                        <button type="submit" name="delete_user" value="<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kullanıcı silinsin mi?')">Sil</button>
                    </td>
                </form>
            </tr>
            <?php endif; endforeach; ?>
            <!-- Yeni kullanıcı ekle -->
            <tr>
                <form method="post">
                    <td></td>
                    <td>
                        <input type="text" name="user_name" class="form-control d-inline w-auto" style="min-width:120px;" placeholder="Ad Soyad">
                        <input type="text" name="user_phone" class="form-control d-inline w-auto" style="min-width:120px;" placeholder="Telefon">
                        <input type="hidden" name="user_group_id" value="<?= $g['id'] ?>">
                    </td>
                    <td>
                        <button type="submit" name="add_user" class="btn btn-sm btn-primary">Ekle</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        <!-- Grupta olmayan kullanıcılar -->
        <?php $ungrouped = array_filter($users, fn($u) => !$u['group_id']);
        if ($ungrouped): ?>
        <tr class="table-secondary"><td colspan="3"><b>Grupta Olmayanlar</b></td></tr>
        <?php foreach ($ungrouped as $u): ?>
        <tr>
            <form method="post" style="display:inline;">
                <td></td>
                <td>
                    <input type="text" name="user_name" value="<?= htmlspecialchars($u['name']) ?>" class="form-control d-inline w-auto" style="min-width:120px;">
                    <input type="text" name="user_phone" value="<?= htmlspecialchars($u['phone']) ?>" class="form-control d-inline w-auto" style="min-width:120px;">
                    <input type="hidden" name="user_group_id" value="">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                </td>
                <td style="display:flex; flex-wrap:wrap; gap:4px;">
                    <button type="submit" name="update_user" class="btn btn-sm btn-success">Kaydet</button>
                    <button type="submit" name="delete_user" value="<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kullanıcı silinsin mi?')">Sil</button>
                </td>
            </form>
            <!-- Grupta olmayan kullanıcıyı gruba ata -->
            <?php foreach ($groups as $g):
                // Sorumlu hariç grup üye sayısı
                $group_member_count = 0;
                foreach ($users as $gu) {
                    if ($gu['group_id'] == $g['id'] && $gu['id'] != $g['captain_id']) $group_member_count++;
                }
                if ($group_member_count < 4): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="assign_user_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="assign_group_id" value="<?= $g['id'] ?>">
                    <button type="submit" name="assign_to_group" class="btn btn-sm btn-primary" style="margin-top:2px;">→ <?= htmlspecialchars($g['name']) ?></button>
                </form>
            <?php endif; endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <!-- Yeni kullanıcı ekle (grupsuz) -->
        <tr>
            <form method="post">
                <td></td>
                <td>
                    <input type="text" name="user_name" class="form-control d-inline w-auto" style="min-width:120px;" placeholder="Ad Soyad">
                    <input type="text" name="user_phone" class="form-control d-inline w-auto" style="min-width:120px;" placeholder="Telefon">
                    <input type="hidden" name="user_group_id" value="">
                </td>
                <td>
                    <button type="submit" name="add_user" class="btn btn-sm btn-primary">Ekle</button>
                </td>
            </form>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>
