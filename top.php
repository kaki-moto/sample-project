<?php
session_start();

// セッションから値を取得
$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? $_SESSION['user_name'] : '';

// ログアウト処理
if (isset($_GET['logout'])){
    // セッションを破棄
    session_destroy();
    header('Location: top.php');
    exit;
}

// セッションを使用してログイン状態をチェック
// login.phpでログイン成功時に$_SESSION['user_id']にDBから取得した$user['id']格納している。$_SESSION['user_name']も同様に保存。
if(isset($_SESSION['user_id'])){
    // ログイン済み（成功してtop.phpに遷移）の場合
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
    <title>トップページ</title>
</head>

<body>
    <header>

        <!-- ログイン状態 -->
            <?php if($loggedIn): ?>
                <p>ようこそ <?php echo htmlspecialchars($userName); ?> 様</p>
                <a href="thread.php">スレッド一覧</a>
                <a href="thread_regist.php">新規スレッド作成</a>
                <!-- クリックでログアウト状態のtop.phpへ -->
                <!-- 単にtop.phpに遷移するだけでなく、ログアウト処理を呼び出すこと -->
                <a href="top.php?logout=1">ログアウト</a>

            <!-- ログアウト状態 -->
            <?php else: ?>
                <a href="thread.php">スレッド一覧</a>
                <a href="member_regist.php">新規会員登録</a>
                <a href="login.php">ログイン</a>
            <?php endif; ?>

    </header>
    <main>

    </main>
</body>
</html>