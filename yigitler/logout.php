<?php
session_start();
session_destroy();
setcookie('remember_user', '', time() - 3600, '/'); // Cookie sil
header('Location: index.php');
exit;
?>