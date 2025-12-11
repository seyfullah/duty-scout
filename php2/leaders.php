<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

$stmt = $pdo->query("SELECT u.name, g.name as group_name, 
    SUM(COALESCE(s.sabah,0) + COALESCE(s.ogle,0) + COALESCE(s.ikindi,0) + COALESCE(s.aksam,0) + COALESCE(s.yatsi,0)) as total
    FROM users u
    JOIN groups g ON u.group_id = g.id
    JOIN scores s ON u.id = s.user_id
    WHERE s.date BETWEEN '$month_start' AND '$month_end'
    GROUP BY u.id
    ORDER BY total DESC
    LIMIT 10");
$leaders = $stmt->fetchAll();

function trend_icon($today, $yesterday) {
    if ($today < $yesterday) {
        return "<span class='text-success'><i class='bi bi-arrow-up'></i> +" . ($yesterday-$today) . "</span>";
    } elseif ($today > $yesterday) {
        return "<span class='text-danger'><i class='bi bi-arrow-down'></i> -" . ($today-$yesterday) . "</span>";
    } else {
        return "<span class='text-secondary'><i class='bi bi-arrow-right'></i> 0</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Liderler Panosu (Aylık)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid full-screen">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-md-6 col-lg-4">
            <?php $active_page = 'leaders'; include 'includes/header.php'; ?>
            <h4 class="mb-3 text-center">Liderler Panosu (Aylık)</h4>
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Sıra</th>
                        <th>İsim</th>
                        <th>Grup</th>
                        <th>Puan</th>
                        <th>Δ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $prev_total = null;
                    foreach ($leaders as $i => $row): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['group_name']) ?></td>
                        <td><?= $row['total'] ?></td>
                        <td>
                            <?php
                            if ($i == 0) {
                                echo "<span class='text-muted'>-</span>";
                            } else {
                                echo trend_icon($row['total'], $leaders[$i-1]['total']);
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</body>
</html>
