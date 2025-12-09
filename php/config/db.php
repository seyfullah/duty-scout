<?php
// config/db.php
// Düzenle: $host, $db, $user, $pass değerlerini kendi ortamına göre ayarla.

$host = '127.0.0.1';
$db   = 'pusulade_namaz';
$user = 'pusulade_namaz';
$pass = 'Cs@(G;WQAhw6';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Prod ortamında hata mesajını direkt göstermeyin
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
