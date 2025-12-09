<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/MemberController.php';
include 'navbar.php';

$ctrl = new MemberController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $ctrl->store($name);
    }
    header("Location: members.php");
    exit;
}

if (isset($_GET['delete'])) {
    $ctrl->delete((int)$_GET['delete']);
    header("Location: members.php");
    exit;
}

$members = $ctrl->index();
?>

<h2>Üyeler</h2>

<form method="post" class="mb-3">
  <input type="hidden" name="action" value="create">
  <div class="input-group mb-2">
    <input type="text" name="name" class="form-control" placeholder="Yeni üye adı" required>
    <button class="btn btn-primary" type="submit">Ekle</button>
  </div>
</form>

<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Ad</th><th>Oluşturulma</th><th>İşlem</th></tr></thead>
  <tbody>
    <?php foreach ($members as $m): ?>
      <tr>
        <td><?= htmlspecialchars($m['id']) ?></td>
        <td><?= htmlspecialchars($m['name']) ?></td>
        <td><?= htmlspecialchars($m['created_at']) ?></td>
        <td>
          <a href="members.php?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silinsin mi?')">Sil</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</div>
