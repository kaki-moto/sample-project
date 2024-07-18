<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>スレッド作成フォーム</title>
</head>
<body>

    <div class="thread-create">
        <h3>スレッド作成フォーム</h3>

        <form action="thread_confirm.php" method="post">

            <label for="title">
                スレッドタイトル
                <p><input></p>
            </label>
            <br>
            <label for="coment">
                コメント
                <p><textarea></textarea></p>
            </label>
            <br>

            <!-- 遷移先はformタグのaction属性で指定 -->
            <p><input type="submit" value="確認画面へ"></p>
        </form>

        <form action="top.php" method="get">
            <input type="submit" value="トップに戻る">
        </form>
    </div>
</body>
</html>