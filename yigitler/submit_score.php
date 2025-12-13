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

// Yeni tabloya uygun puan giriş kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $fields = [];
    foreach ($prayer_points as $prayer => $base_points) {
        if (isset($_POST['prayer'][$prayer])) {
            $value = $_POST['prayer'][$prayer];
            $fields[$prayer] = ($value === 'jamaah') ? $base_points * 2 : $base_points;
        } else {
            $fields[$prayer] = null;
        }
    }
    // Aynı gün için daha önce kayıt var mı?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM scores WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $date]);
    $exists = $stmt->fetchColumn();
    if ($exists) {
        // Güncelle
        $stmt = $pdo->prepare("UPDATE scores SET sabah=?, ogle=?, ikindi=?, aksam=?, yatsi=? WHERE user_id=? AND date=?");
        $stmt->execute([
            $fields['sabah'], $fields['ogle'], $fields['ikindi'], $fields['aksam'], $fields['yatsi'],
            $user_id, $date
        ]);
        $success = "Namaz puanları güncellendi!";
        echo '<meta http-equiv="refresh" content="2;url=dashboard.php">';
    } else {
        // Ekle
        $stmt = $pdo->prepare("INSERT INTO scores (user_id, date, sabah, ogle, ikindi, aksam, yatsi) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, $date,
            $fields['sabah'], $fields['ogle'], $fields['ikindi'], $fields['aksam'], $fields['yatsi']
        ]);
        $success = "Namaz puanları kaydedildi!";
        echo '<meta http-equiv="refresh" content="2;url=dashboard.php">';
    }
}

// Günlük puanlar (bugün ve dün)
// Yeni tabloya uygun günlük puanlar
function get_day_scores($pdo, $user_id, $date) {
    $stmt = $pdo->prepare("SELECT sabah, ogle, ikindi, aksam, yatsi FROM scores WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $date]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
                                foreach ($prayer_points as $prayer => $point):
                                    $score = isset($selected_scores[$prayer]) ? $selected_scores[$prayer] : null;
                                    $checked_normal = ($score == $point) ? 'checked' : '';
                                    $checked_jamaah = ($score == $point * 2) ? 'checked' : '';
                                ?>
                                <tr>
                                    <td><?= ucfirst($prayer) ?> (<?= $point ?>)</td>
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