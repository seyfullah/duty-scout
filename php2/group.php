<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Gruplar arası sıralama
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$stmt = $pdo->query("SELECT g.name as group_name, SUM(s.points) as total
    FROM groups g
    JOIN users u ON g.id = u.group_id
    JOIN scores s ON u.id = s.user_id
    WHERE s.date BETWEEN '$month_start' AND '$month_end'
    GROUP BY g.id
    ORDER BY total DESC");
$group_leaders = $stmt->fetchAll();

// Grup içi sıralama
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT group_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$group_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT u.name, SUM(s.points) as total
    FROM users u
    JOIN scores s ON u.id = s.user_id
    WHERE u.group_id = ? AND s.date BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY total DESC");
$stmt->execute([$group_id, $month_start, $month_end]);
$group_members = $stmt->fetchAll();

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT u.name, g.name as group_name FROM users u LEFT JOIN groups g ON u.group_id = g.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Grup Sıralamaları</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html { height: 100%; }
        .full-screen { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .table-responsive { max-height: 50vh; overflow-y: auto; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid full-screen">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-10 col-lg-8">
            <?php $active_page = 'group'; include 'includes/header.php'; ?>
            <h5 class="mb-3 text-center">Gruplar Arası Sıralama (Aylık)</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Sıra</th>
                            <th>Grup</th>
                            <th>Puan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group_leaders as $i => $row): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($row['group_name']) ?></td>
                            <td><?= $row['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($group_leaders)): ?>
                        <tr><td colspan="3" class="text-center">Henüz veri yok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <h5 class="mb-3 text-center">Grup İçi Sıralama (Aylık)</h5>
            <div class="table-responsive mb-4">
                <table class="table table-striped align-middle text-center">
                    <thead>
                        <tr>
                            <th>Sıra</th>
                            <th>İsim</th>
                            <th>Puan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group_members as $i => $row): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($group_members)): ?>
                        <tr><td colspan="3" class="text-center">Henüz veri yok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>