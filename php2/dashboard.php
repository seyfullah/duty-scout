<?php
$prayer_points = [
    'sabah' => 5,
    'ogle' => 3,
    'ikindi' => 3,
    'aksam' => 3,
    'yatsi' => 4
];
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

// Yeni scores tablosuna göre verileri çek
$stmt = $pdo->prepare("
    SELECT date, sabah, ogle, ikindi, aksam, yatsi
    FROM scores
    WHERE user_id = ? AND date BETWEEN ? AND ?
    ORDER BY date DESC
");
$stmt->execute([$user_id, $month_start, $month_end]);
$rows = $stmt->fetchAll();

// Verileri gün bazında grupla
$days = [];
foreach ($rows as $row) {
    $d = $row['date'];
    $days[$d] = [
        'sabah' => $row['sabah'],
        'ogle' => $row['ogle'],
        'ikindi' => $row['ikindi'],
        'aksam' => $row['aksam'],
        'yatsi' => $row['yatsi']
    ];
}

// Tüm günler için toplam puan ve artış/azalış hesapla
$prayers = ['sabah','ogle','ikindi','aksam','yatsi'];
$dates = array_keys($days);
usort($dates, fn($a, $b) => strcmp($b, $a)); // Azalan sırada

$totals = [];
foreach ($dates as $date) {
    $total = 0;
    foreach ($prayers as $p) {
        if (isset($days[$date][$p]) && $days[$date][$p] !== null) $total += $days[$date][$p];
    }
    $totals[$date] = $total;
}


// Artış/azalış sütunu için (önceki güne göre)
function trend_icon($today, $yesterday) {
    if ($today > $yesterday) {
        return "<span class='text-success fw-bold'><i class='bi bi-arrow-up'></i> +" . ($today-$yesterday) . "</span>";
    } elseif ($today < $yesterday) {
        return "<span class='text-danger fw-bold'><i class='bi bi-arrow-down'></i> -" . ($yesterday-$today) . "</span>";
    } else {
        return "<span class='text-secondary'><i class='bi bi-arrow-right'></i> 0</span>";
    }
}

// Liderler panosu (ilk 10) - grup adı da çekiliyor
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

// Grup renkleri
function group_color($group) {
    switch (mb_strtolower($group)) {
        case 'pusula': return 'table-primary';
        case 'isgd': return 'table-success';
        case 'gülistan': return 'table-warning';
        default: return '';
    }
}

// Hicri tarih ve gün adını ay ve gün numarasıyla birlikte döndürür

function format_date_short($date) {
    $days = ['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'];
    $gun = $days[date('w', strtotime($date))];
    $aylar = [
        '01' => 'Oca', '02' => 'Şub', '03' => 'Mar', '04' => 'Nis', '05' => 'May', '06' => 'Haz',
        '07' => 'Tem', '08' => 'Ara', '09' => 'Eyl', '10' => 'Eki', '11' => 'Kas', '12' => 'Ara'
    ];
    $t = strtotime($date);
    $gun_sayi = date('d', $t);
    $ay = $aylar[date('m', $t)];
    return $gun_sayi . ' ' . $ay . ' ' . $gun;
}

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT u.name, g.name as group_name FROM users u LEFT JOIN groups g ON u.group_id = g.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Aylık Namaz Tablosu & Liderler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body, html { height: 100%; }
        .full-screen { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .table-responsive { max-height: 60vh; overflow-y: auto; }
        .cemaat { font-size: 1.2em; font-weight: bold; color: #0dcaf0; }
        .table-head-small th { font-size: 0.95em; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid full-screen">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-md-6 col-lg-4">
            <?php $active_page = 'dashboard'; include 'includes/header.php'; ?>
            <h4 class="mb-3 text-center">Aylık Namaz Puan Tablosu</h4>
            <?php
            $total_days = count($dates);
            ?>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-light table-head-small">
                        <tr>
                            <th>Gün</th>
                            <th>Tarih</th>
                            <th>S</th>
                            <th>Ö</th>
                            <th>İ</th>
                            <th>A</th>
                            <th>Y</th>
                            <th>Top</th>
                            <th>Δ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $prev_total = null;
                        $total_days = count($dates);
                        foreach ($dates as $i => $date):
                            $hicri_gun = $total_days - $i;
                        ?>
                            <tr>
                                <td><?= $hicri_gun ?></td>
                                <td style="white-space:nowrap"><?= format_date_short($date) ?></td>
                                <?php foreach ($prayers as $p): ?>
                                    <td>
                                        <?php
                                        if (isset($days[$date][$p]) && $days[$date][$p] !== null) {
                                            $pt = $days[$date][$p];
                                            // Cemaatle mi?
                                            $base = $prayer_points[$p];
                                            if ($pt == $base * 2) {
                                                echo "<span class='cemaat'>$pt</span>";
                                            } else {
                                                echo $pt;
                                            }
                                        } else {
                                            echo "-";
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="fw-bold"><?= $totals[$date] ?></td>
                                <td>
                                    <?php
                                    if ($prev_total !== null) {
                                        echo trend_icon($totals[$date], $prev_total);
                                    } else {
                                        echo "<span class='text-muted'>-</span>";
                                    }
                                    $prev_total = $totals[$date];
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach;
                        if (empty($dates)): ?>
                            <tr><td colspan="9" class="text-center">Bu ay için puan kaydınız yok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <hr>
            <h5 class="mb-2">Liderler Panosu (Aylık)</h5>
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Sıra</th>
                        <th>İsim</th>
                        <th>Grup</th>
                        <th>Puan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaders as $i => $row): ?>
                    <tr class="<?= group_color($row['group_name']) ?>">
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['group_name']) ?></td>
                        <td><?= $row['total'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Grup üyelerinin puanlarını göster (sadece kaptan görebilir)
            // Kullanıcı kaptan mı?
            $stmt = $pdo->prepare("SELECT id FROM groups WHERE captain_id = ?");
            $stmt->execute([$user_id]);
            $group = $stmt->fetch();

            if ($group) {
                // Kaptanın grubundaki üyeleri ve puanlarını çek (her üye bir kez, sadece kendi grubu)
                $group_id = $group['id'];
                $month_start = date('Y-m-01');
                $month_end = date('Y-m-t');
                $stmt = $pdo->prepare("
                    SELECT u.name, SUM(COALESCE(s.sabah,0) + COALESCE(s.ogle,0) + COALESCE(s.ikindi,0) + COALESCE(s.aksam,0) + COALESCE(s.yatsi,0)) as total
                    FROM users u
                    LEFT JOIN scores s ON u.id = s.user_id AND s.date BETWEEN ? AND ?
                    WHERE u.group_id = ?
                    GROUP BY u.id, u.name
                    ORDER BY total DESC
                ");
                $stmt->execute([$month_start, $month_end, $group_id]);
                $members = $stmt->fetchAll();
                ?>
                <hr>
                <h5 class="mb-2">Grubumdakilerin Aylık Puanları</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>Puan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['total'] ?: 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>