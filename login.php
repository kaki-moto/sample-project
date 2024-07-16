<?php 
session_start();

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// 追加: フォームデータの取得と解放
$formData = $_SESSION['formData'] ?? [];
unset($_SESSION['formData']);

// ログインボタンが押されたら
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // 変更: セッションではなく変数に保存、？
     $formData = $_POST;
    // バリデーション
    if (empty($_POST['email'])) {
        $errors['email'] = '※メールアドレス（ID）を入力してください。';
    }
    if (empty($_POST['pass'])) {
        $errors['pass'] = '※パスワードを入力してください。';
    }

    // エラー（$errors）がなかったら
    if (empty($errors)) {
        // データベース接続
        $dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
        $username = 'root';
        $passwordDb = 'K4aCuFEh';

        try {
            $pdo = new PDO($dsn, $username, $passwordDb);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 入力されたメールアドレスとパスワードを取得、['inputのname属性']
            $email = $_POST['email'];
            $password = $_POST['pass'];

             // データベースでユーザーを検索
             $stmt = $pdo->prepare("SELECT * FROM members WHERE email = :email");
             $stmt->bindParam(':email', $email);
             $stmt->execute();

             if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                // パスワードの照合
                if (password_verify($password, $user['password'])) {
                    // ログイン成功
                    $_SESSION['user_id'] = $user['id'];
                    header('Location: top.php');
                    exit();
                } else {
                    $errors['login'] = 'メールアドレスまたはパスワードが間違っています。';
                }
            } else {
                $errors['login'] = 'メールアドレスまたはパスワードが間違っています。';
            }
        // データベース接続に失敗したら
        } catch (PDOException $e) {
            $errors['database'] = 'データベースエラー: ' . $e->getMessage();
        }
    }

    // エラーがなかったら
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['formData'] = $formData;
        // login.phpに遷移
        header('Location: login.php');
        exit();
    }
}
?>

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
                <!-- value でログインボタン押してエラーになった後も値が保持されるように-->
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? '', ENT_QUOTES); ?>">
                <?php if (isset($errors['email'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['email']); ?></p>
                <?php endif; ?>
            </label>
            <br>
            <label for="pass">
                パスワード
                <input type="password" id="pass" name="pass">
                <?php if (isset($errors['pass'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['pass']); ?></p>
                <?php endif; ?>
            </label>

            <p><input type="submit" value="ログイン"></p>

        </form>

        <form action="top.php" method="get">
            <input type="submit" value="トップに戻る">
        </form>
</body>
</html>