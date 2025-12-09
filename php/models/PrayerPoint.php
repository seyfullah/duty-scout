<?php
// models/PrayerPoint.php
class PrayerPoint {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM prayer_points ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function create($text) {
        $stmt = $this->pdo->prepare("INSERT INTO prayer_points (text) VALUES (?)");
        return $stmt->execute([$text]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM prayer_points WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
