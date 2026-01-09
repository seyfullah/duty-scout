<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Kullanıcı bilgisi ve aktif sayfa
if (!isset($user_info)) {
    if (isset($pdo) && isset($user_id)) {
        $stmt = $pdo->prepare("SELECT u.name, g.name as group_name, u.is_admin FROM users u LEFT JOIN groups g ON u.group_id = g.id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        if (!isset($_SESSION['is_admin'])) {
            $_SESSION['is_admin'] = !empty($user_info['is_admin']);
        }
    } else {
        $user_info = ['name' => '', 'group_name' => ''];
    }
}
$active = isset($active_page) ? $active_page : '';
?>
<nav class="mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <span class="fw-bold"><?= htmlspecialchars($user_info['name']) ?></span>
            <?php if (!empty($user_info['group_name'])): ?>
                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($user_info['group_name']) ?></span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="users_admin.php" class="btn btn-outline-dark btn-sm<?= $active=='users_admin' ? ' fw-bold text-dark border-dark' : '' ?>">Yönetici</a>
            <?php endif; ?>
              <a href="dashboard.php" class="btn btn-outline-primary btn-sm<?= $active=='dashboard' ? ' fw-bold text-dark border-dark' : '' ?>">Ana Sayfa</a>
            <a href="submit_score.php" class="btn btn-outline-success btn-sm<?= $active=='submit_score' ? ' fw-bold text-dark border-dark' : '' ?>">Puan Gir</a>
            <a href="group_order.php" class="btn btn-outline-secondary btn-sm<?= $active=='group' ? ' fw-bold text-dark border-dark' : '' ?>">Grup Sıralama</a>
            <a href="leaders.php" class="btn btn-outline-warning btn-sm<?= $active=='leaders' ? ' fw-bold text-dark border-dark' : '' ?>">Liderler Panosu</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Çıkış</a>
        </div>
    </div>
</nav>
