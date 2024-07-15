<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ログインフォーム</title>
</head>
<body>
    <div class="login-form">
        <h3>ログイン</h3>
        <form action="login.php" method="post">
            <label for="email">
                メールアドレス（ID）
                <input type="text" id="email" name="email">
            </label>
            <br>
            <label for="pass">
                パスワード
                <input type="password" id="pass" name="pass">
            </label>
            <br>
            <button type="submit">ログイン</button>
            <br>
        </form>
        <form action="top.php" method="get">
            <input type="submit" value="トップに戻る">
        </form>
</body>
</html>