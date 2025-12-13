<?php
$host = 'localhost';
$db   = 'pusulade_yigitler';
$user = 'pusulade_yigitler';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Set Turkish collation for connection
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_turkish_ci'");
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>