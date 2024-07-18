<?php
session_start();

// フォームが送信される。全て一旦セッションに保存。バリデーション実行。エラー発生したらエラーメッセージをセッションに保存し、同じページにリダイレクト。
// ページがリロードされる時、セッションからエラーメッセージを取り出して表示。エラーメッセージをセッションから削除。

// 初期化
$errors = [];

// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// バリデーション
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['formData'] = $_POST;

    // スレッドタイトルのバリデーション
    if (empty(trim($_POST['title']))) {
        $errors['title'] = '※スレッドタイトルを入力してください。';
    } elseif (mb_strlen($_POST['title']) > 100) {
        $errors['title'] = '※スレッドタイトルは100文字以内で入力してください。';
    }

    // コメントのバリデーション
    if (empty(trim($_POST['comment']))) {
        $errors['comment'] = '※コメントを入力してください。';
    } elseif (mb_strlen($_POST['comment']) > 500) {
        $errors['comment'] = '※コメントは500文字以内で入力してください。';
    }

    // エラーがなければ確認画面へ
    if (empty($errors)) {
        header('Location: thread_confirm.php');
        exit();
    } else {
        // エラーがあればエラーメッセージをセッションに保存し、再表示
        $_SESSION['errors'] = $errors;
        header('Location: thread_regist.php');
        exit();
    }
}

// isset()で、$_SESSION['formData']と$_SESSION['errors']の存在をチェック
// 存在する場合はその値を代入、存在しない場合は空の配列[]を変数に代入。
$formData = $_SESSION['formData'] ?? [];
$errors = $_SESSION['errors'] ?? [];

// セッションのクリア、エラーを格納していた変数を削除、エラーメッセージをセッションから削除
unset($_SESSION['errors']);
unset($_SESSION['formData']);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>スレッド作成フォーム</title>
</head>
<body>

    <div class="thread-create">
        <h3>スレッド作成フォーム</h3>

        <form action="thread_regist.php" method="post">

            <label for="title">
                スレッドタイトル
                <!-- value属性は、リロード後も表示されるように -->
                <p><input type="text" name="title" value="<?php echo htmlspecialchars($formData['title'] ?? '', ENT_QUOTES); ?>"></p>
                <!-- 赤文字でエラー出力 -->
                <?php if (isset($errors['title'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['title'], ENT_QUOTES); ?></p>
                <?php endif; ?>
            </label>
            <br>
            <label for="comment">
                コメント
                <!-- リロード後も表示されるように -->
                <p><textarea name="comment"><?php echo htmlspecialchars($formData['comment'] ?? '', ENT_QUOTES); ?></textarea></p>
                <!-- 赤文字でエラー出力 -->
                <?php if (isset($errors['comment'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['comment'], ENT_QUOTES); ?></p>
                <?php endif; ?>
            </label>
            <br>

            <!-- 遷移先はformタグのaction属性で指定 -->
            <p><input type="submit" value="確認画面へ"></p>
        </form>

        <form action="thread.php" method="get">
            <input type="submit" value="一覧にもどる">
        </form>

        <form action="top.php" method="get">
            <input type="submit" value="トップに戻る">
        </form>
    </div>
</body>
</html>