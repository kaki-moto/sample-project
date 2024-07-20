<?php 
session_start();
date_default_timezone_set('Asia/Tokyo'); //日本時間に

// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$passworddb = 'K4aCuFEh';

// 退会するボタンが押されてリロードしてPOST受け取ったら
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DBへ接続して退会処理（ソフトデリート）を行っていく。
    // DBに接続できたかできてないかわかるようにするためtry…catch文
    try {
        $pdo = new PDO($dsn, $username, $passworddb);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 退会処理　membersテーブルのdeleted_atカラムに削除日時を挿入
        $member_id = $_SESSION['user_id'];
        $now = date('Y-m-d H:i:s');
        // 退会日時を設定
        $stmt = $pdo->prepare("UPDATE members SET deleted_at = :deleted_at WHERE id = :id"); //会員ID
        $stmt->execute([
            ':deleted_at' => $now,
            ':id' => $member_id
        ]);

        // セッションを破棄
        session_unset();
        session_destroy();

        // DBで退会処理（ソフトデリート）成功、退会処理が成功すればtop.phpに遷移
        header('Location: top.php');
        exit;   

    // DBに接続できなかった時の処理
    } catch (PDOException $e) {
        echo '退会処理中にエラーが発生しました: ' . $e->getMessage();
    }
}


?>


<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>退会ページ</title>
</head>

<body>
    <header>
        <form action="top.php" method="get">
            <input type="submit" value="トップに戻る">
        </form>
    </header>

    <main>
        <h3>退会</h3>
        <p>退会しますか？</p>

        <form action="member_withdrawal.php" method="post">
        <input type="submit" value="退会する">
        </form>

    </main>


</body>
</html>