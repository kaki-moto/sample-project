<?php
session_start();

// admin_top.phpはログインしてる管理者だけが見れるように?
// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// セッションから値を取得
$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? $_SESSION['user_name'] : '';

// ログアウト処理
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy(); // セッションを破棄
    header('Location: login.php'); // ログインページにリダイレクト
    exit();
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
<html>
<head>
    <meta charset="utf-8">
    <title>管理画面トップ</title>
</head>
<body>
    <header>
        <strong>掲示板管理画面メインメニュー</strong>

        <!-- ログイン状態 -->
        <?php if($loggedIn): ?>
            ようこそ<?php echo htmlspecialchars($_SESSION['user_name']); ?>さん
            <!-- クリックでログアウト状態のtop.phpへ -->
            <!-- 単にadmin内の管理者用admin_top.phpをリロードするのではなく、ログアウト処理を呼び出すこと -->
            <a href="admin_top.php?logout=1">ログアウト</a>
        <?php endif; ?>
    </header>

    <main>
        <form action="member.php" method="get">
            <input type="submit" value="会員一覧">
        </form>
    </main>

</body>
</html>