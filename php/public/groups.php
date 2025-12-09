<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/GroupController.php';
include 'navbar.php';

$ctrl = new GroupController($pdo);

// POST: oluştur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $ctrl->store($name);
    }
    header("Location: groups.php");
    exit;
}

// Sil
if (isset($_GET['delete'])) {
    $ctrl->delete((int)$_GET['delete']);
    header("Location: groups.php");
    exit;
}

$groups = $ctrl->index();
?>

<h2>Gruplar</h2>

<form method="post" class="mb-3">
  <input type="hidden" name="action" value="create">
  <div class="input-group mb-2">
    <input type="text" name="name" class="form-control" placeholder="Yeni grup adı" required>
    <button class="btn btn-primary" type="submit">Ekle</button>
  </div>
</form>

<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Ad</th><th>Oluşturulma</th><th>İşlem</th></tr></thead>
  <tbody>
    <?php foreach ($groups as $g): ?>
      <tr>
        <td><?= htmlspecialchars($g['id']) ?></td>
        <td><?= htmlspecialchars($g['name']) ?></td>
        <td><?= htmlspecialchars($g['created_at']) ?></td>
        <td>
          <a href="groups.php?delete=<?= $g['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silinsin mi?')">Sil</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</div>
