<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/PrayerPointController.php';
include 'navbar.php';

$ctrl = new PrayerPointController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $text = trim($_POST['text'] ?? '');
    if ($text !== '') {
        $ctrl->store($text);
    }
    header("Location: prayer_points.php");
    exit;
}

if (isset($_GET['delete'])) {
    $ctrl->delete((int)$_GET['delete']);
    header("Location: prayer_points.php");
    exit;
}

$points = $ctrl->index();
?>

<h2>Dua Noktaları</h2>

<form method="post" class="mb-3">
  <input type="hidden" name="action" value="create">
  <div class="input-group mb-2">
    <input type="text" name="text" class="form-control" placeholder="Dua metni" required>
    <button class="btn btn-primary" type="submit">Ekle</button>
  </div>
</form>

<ul class="list-group">
  <?php foreach ($points as $p): ?>
    <li class="list-group-item d-flex justify-content-between align-items-start">
      <div><?= nl2br(htmlspecialchars($p['text'])) ?></div>
      <div>
        <a href="prayer_points.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silinsin mi?')">Sil</a>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

</div>
