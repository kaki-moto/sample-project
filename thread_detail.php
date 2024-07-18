<?php
session_start();

// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // スレッドIDを取得
    if (isset($_GET['id'])) {
        $thread_id = $_GET['id'];

        // スレッド情報を取得
        $stmt = $pdo->prepare("SELECT threads.*, CONCAT(members.name_sei, ' ', members.name_mei) AS member_name FROM threads JOIN members ON threads.member_id = members.id WHERE threads.id = :id");
        $stmt->bindParam(':id', $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thread) {
            echo "スレッドが見つかりません。";
            exit;
        }
    } else {
        echo "スレッドIDが指定されていません。";
        exit;
    }
} catch (PDOException $e) {
    echo '接続失敗: ' . $e->getMessage();
    exit;
}


// セッションを使用してログイン状態をチェック
// login.phpでログイン成功時に$_SESSION['user_id']にDBから取得した$user['id']を格納している。$_SESSION['user_name']も同様に保存。
if(isset($_SESSION['user_id'])){
    // ログイン済みの場合
    $loggedIn = true;
} else {
    // 未ログインの場合
    $loggedIn = false;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>スレッド一覧</title>
</head>
<body>
    <header>
        <a href="thread.php">スレッド一覧にもどる</a>
    </header>
    <main>
        <!-- DB接続してスレタイ・登録日時 -->
        <h3><?php echo htmlspecialchars($thread['title']); ?></h3>
        <p><?php echo htmlspecialchars($thread['created_at']); ?></p>

        <!-- スレ作成時の作成者名・コメント・登録日時 -->
         投稿者：<?php echo htmlspecialchars($thread['member_name']); ?>
         <?php echo htmlspecialchars($thread['created_at']); ?>
         <p><?php echo htmlspecialchars($thread['content']); ?></p>

        <!-- ログイン時のみコメント投稿フォーム表示 -->
        <?php if($loggedIn): ?>
        <form action="thread_detail.php?id=<?php echo htmlspecialchars($thread['id']); ?>" method="POST">
            <textarea name="reaction"></textarea>
            <p><input type="submit" value="コメントする"></p>
        </form>
        <?php endif;?>
        
    </main>
</body>
</html>
