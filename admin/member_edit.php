<?php
session_start();

// ログインしてる管理者だけが見れるように
// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

?>
