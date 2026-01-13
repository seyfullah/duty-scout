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

function format_phone($phone)
{
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 10) {
        return preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/', '$1 $2 $3 $4', $digits);
    }
    return $phone;
}
?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-W1XMEGK62C"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-W1XMEGK62C');
</script>
<nav class="mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <span class="fw-bold"><?= htmlspecialchars($user_info['name']) ?></span>
            <?php if (!empty($user_info['group_name'])): ?>
                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($user_info['group_name']) ?></span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="/yigitler/dashboard.php" class="btn btn-outline-primary btn-sm<?= $active == 'dashboard' ? ' fw-bold text-dark border-dark' : '' ?>">Ana Sayfa</a>
            <a href="/yigitler/submit_score.php" class="btn btn-outline-success btn-sm<?= $active == 'submit_score' ? ' fw-bold text-dark border-dark' : '' ?>">Puan Gir</a>
            <a href="/yigitler/group_order.php" class="btn btn-outline-secondary btn-sm<?= $active == 'group' ? ' fw-bold text-dark border-dark' : '' ?>">Grup Sıralama</a>
            <a href="/yigitler/leaders.php" class="btn btn-outline-warning btn-sm<?= $active == 'leaders' ? ' fw-bold text-dark border-dark' : '' ?>">Liderler Panosu</a>
            <a href="/yigitler/logout.php" class="btn btn-outline-danger btn-sm">Çıkış</a>
        </div>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <div class="d-flex gap-2">
                <a href="/yigitler/admin/users.php" class="btn btn-outline-primary btn-sm<?= $active == 'users' ? ' fw-bold text-dark border-dark' : '' ?>">Kullanıcılar</a>
                <a href="/yigitler/admin/groups.php" class="btn btn-outline-success btn-sm<?= $active == 'groups' ? ' fw-bold text-dark border-dark' : '' ?>">Gruplar</a>
                <a href="/yigitler/admin/schools.php" class="btn btn-outline-success btn-sm<?= $active == 'schools' ? ' fw-bold text-dark border-dark' : '' ?>">Okullar</a>
                <a href="/yigitler/admin/ngos.php" class="btn btn-outline-success btn-sm<?= $active == 'ngos' ? ' fw-bold text-dark border-dark' : '' ?>">STK'lar</a>
                <a href="/yigitler/admin/group_users.php" class="btn btn-outline-secondary btn-sm<?= $active == 'group_users' ? ' fw-bold text-dark border-dark' : '' ?>">Grup Kullanıcıları</a>
            </div>
        <?php endif; ?>
    </div>
</nav>