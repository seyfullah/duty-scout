<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/MemberController.php';
require_once __DIR__ . '/../controllers/GroupController.php';
require_once __DIR__ . '/../controllers/MemberController.php';
include 'navbar.php';

$gmCtrl = new MemberController($pdo);
$groupCtrl = new GroupController($pdo);
$memberCtrl = new MemberController($pdo);

// create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $group_id = (int)($_POST['group_id'] ?? 0);
    $member_id = (int)($_POST['member_id'] ?? 0);
    if ($group_id && $member_id) {
        $gmCtrl->store($group_id, $member_id);
    }
    header("Location: group_members.php");
    exit;
}

if (isset($_GET['delete'])) {
    $gmCtrl->delete((int)$_GET['delete']);
    header("Location: group_members.php");
    exit;
}

$list = $gmCtrl->index();
$groups = $groupCtrl->index();
$members = $memberCtrl->index();
?>

<h2>Grup Üyeleri</h2>

<form method="post" class="mb-3">
  <input type="hidden" name="action" value="create">
  <div class="row g-2">
    <div class="col-auto">
      <select name="group_id" class="form-select" required>
        <option value="">-- Grup seç --</option>
        <?php foreach ($groups as $g): ?>
          <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <select name="member_id" class="form-select" required>
        <option value="">-- Üye seç --</option>
        <?php foreach ($members as $m): ?>
          <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">Ekle</button>
    </div>
  </div>
</form>

<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Grup</th><th>Üye</th><th>Oluşturulma</th><th>İşlem</th></tr></thead>
  <tbody>
    <?php foreach ($list as $row): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['group_name']) ?></td>
        <td><?= htmlspecialchars($row['member_name']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
          <a href="group_members.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silinsin mi?')">Sil</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</div>
