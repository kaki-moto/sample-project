<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>スレッド作成フォーム</title>
</head>
<body>

    <div class="thread_confirm">
        <h3>スレッド作成確認画面</h3>

        <form action="thread.php" method="post">

            <label for="title">
                スレッドタイトル
                <!-- セッションからスレッドタイトル取得・表示 -->
                <p><input></p>
            </label>
            <br>
            <label for="coment">
                コメント
                <!-- セッションからコメント取得・表示 -->
                <p><textarea></textarea></p>
            </label>
            <br>

            <!-- 遷移先はformタグのaction属性で指定 -->
            <!-- クリックするとsubmitされるようにする -->
            <p><input type="submit" value="スレッドを作成する"></p>
        </form>

        <form action="thread_regist.php" method="get">
            <input type="submit" value="前にに戻る">
        </form>
    </div>
</body>
</html>