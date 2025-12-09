<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

function format_date($date) {
    return date('d.m.Y', strtotime($date));
}

$prayer_points = [
    'sabah' => 5,
    'ogle' => 3,
    'ikindi' => 3,
    'aksam' => 3,
    'yatsi' => 4
];

// Çoklu namaz puan giriş kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $success_count = 0;
    $error_msgs = [];
    if (isset($_POST['prayer']) && is_array($_POST['prayer'])) {
        foreach ($_POST['prayer'] as $prayer => $value) {
            if (!isset($prayer_points[$prayer])) continue; // Geçersiz namaz ismi
            // Aynı gün ve vakit için daha önce puan girilmiş mi?
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM scores WHERE user_id = ? AND date = ? AND prayer = ?");
            $stmt->execute([$user_id, $date, $prayer]);
            $exists = $stmt->fetchColumn();
            if ($exists) {
                continue;
            } elseif ($date !== $today && $date !== $yesterday) {
                $error_msgs[] = ucfirst($prayer) . ": Sadece bugün ve dün için puan girebilirsiniz.";
                continue;
            } else {
                $base_points = $prayer_points[$prayer];
                $is_jamaah = ($value === 'jamaah') ? 1 : 0;
                $points = $is_jamaah ? $base_points * 2 : $base_points;
                $stmt = $pdo->prepare("INSERT INTO scores (user_id, date, prayer, points, is_jamaah) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $date, $prayer, $points, $is_jamaah]);
                $success_count++;
            }
        }
    }
    if ($success_count > 0) {
        $success = "Namaz puanları kaydedildi!";
    }
    if (!empty($error_msgs)) {
        $error = implode('<br>', $error_msgs);
    }
}

// Günlük puanlar (bugün ve dün)
function get_day_scores($pdo, $user_id, $date) {
    $stmt = $pdo->prepare("SELECT prayer, points, is_jamaah, date FROM scores WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $date]);
    return $stmt->fetchAll();
}
$today_scores = get_day_scores($pdo, $user_id, $today);
$yesterday_scores = get_day_scores($pdo, $user_id, $yesterday);

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT u.name, g.name as group_name FROM users u LEFT JOIN groups g ON u.group_id = g.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Puan Gir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html { height: 100%; }
        .full-screen { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid full-screen">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-6 col-lg-4">
            <?php $active_page = 'submit_score'; include 'includes/header.php'; ?>
            <h4 class="mb-3 text-center">Namaz Puanı Gir</h4>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <!-- Debug kodu kaldırıldı -->
            <?php
            // Seçili tarihe ait puanları göster
            $selected_date = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : $today);
            $selected_scores = get_day_scores($pdo, $user_id, $selected_date);
            ?>
            <form method="post" class="p-3 rounded shadow bg-white mb-4">
                <div class="mb-3">
                    <label for="date" class="form-label">Tarih</label>
                        <select name="date" class="form-control" id="date-select">
                            <option value="<?= $today ?>" <?= ($selected_date == $today ? 'selected' : '') ?>><?= format_date($today) ?></option>
                            <option value="<?= $yesterday ?>" <?= ($selected_date == $yesterday ? 'selected' : '') ?>><?= format_date($yesterday) ?></option>
                        </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Namazlar</label>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Namaz</th>
                                    <th>Kılındı</th>
                                    <th>Cemaatle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Seçili tarihteki puanlar
                                $selected_scores_map = [];
                                foreach ($selected_scores as $row) {
                                    $selected_scores_map[$row['prayer']] = $row;
                                }
                                foreach ($prayer_points as $prayer => $point):
                                    $checked_normal = (isset($selected_scores_map[$prayer]) && !$selected_scores_map[$prayer]['is_jamaah']) ? 'checked' : '';
                                    $checked_jamaah = (isset($selected_scores_map[$prayer]) && $selected_scores_map[$prayer]['is_jamaah']) ? 'checked' : '';
                                ?>
                                <tr>
                                    <td><?= ucfirst($prayer) ?> (<?= $point ?> puan)</td>
                                    <td class="text-center">
                                        <input type="radio" name="prayer[<?= $prayer ?>]" value="normal" id="prayer_<?= $prayer ?>_normal" <?= $checked_normal ?> >
                                        <label for="prayer_<?= $prayer ?>_normal">Kılındı</label>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio" name="prayer[<?= $prayer ?>]" value="jamaah" id="prayer_<?= $prayer ?>_jamaah" <?= $checked_jamaah ?> >
                                        <label for="prayer_<?= $prayer ?>_jamaah">Cemaatle</label>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100">Kaydet</button>
            </form>
                <script>
    document.getElementById('date-select').addEventListener('change', function() {
        var selected = this.value;
        var url = new URL(window.location.href);
        url.searchParams.set('date', selected);
        window.location.href = url.toString();
    });
    </script>
        </div>
    </div>
</div>
</body>
</html>