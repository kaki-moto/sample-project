<?php
session_start();

// DB情報
$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$passwordDb = 'K4aCuFEh';

// バリデーション
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ログインIDのバリデーション
    if (empty($_POST['login_id'])) {
        $errors['login_id'] = '※ログインIDを入力してください。';
    } elseif (mb_strlen($_POST['login_id']) < 7) {
        $errors['login_id'] = '※ログインIDは7文字以上で入力してください。';
    } elseif (mb_strlen($_POST['login_id']) > 10) {
        $errors['login_id'] = '※ログインIDは10文字以内で入力してください。';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['login_id'])) {
        $errors['login_id'] = '※ログインIDは半角英数字で入力してください。';
    } else {
        $_SESSION['login_id'] = $_POST['login_id']; // ログインIDをセッションに保存
    }

    // パスワードのバリデーション
    if (empty($_POST['password'])) {
        $errors['password'] = '※パスワードを入力してください。';
    } elseif (mb_strlen($_POST['password']) < 8) {
        $errors['password'] = '※パスワードは8文字以上で入力してください。';
    } elseif (mb_strlen($_POST['password']) > 20) {
        $errors['password'] = '※パスワードは20文字以内で入力してください。';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['password'])) {
        $errors['password'] = '※パスワードは半角英数字で入力してください。';
    }

    if (empty($errors)) {
        // DB接続と認証処理
        try {
            $pdo = new PDO($dsn, $username, $passwordDb);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $login_id = $_POST['login_id'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM administers WHERE login_id = :login_id AND deleted_at IS NULL");
            $stmt->bindParam(':login_id', $login_id);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    // ログインした管理者ユーザーのDBから取得した情報（管理者idと氏名）をセッションに保存
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    header('Location: admin_top.php');
                    exit();
                } else {
                    $errors['password'] = 'パスワードが間違っています。';
                }
            } else {
                $errors['login_id'] = 'ログインIDが間違っています。';
            }
        } catch (PDOException $e) {
            $errors['db'] = 'データベースエラー: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>管理画面ログイン</title>
</head>
<body>
    <h3>管理画面ログイン</h3>
    <form action="login.php" method="post">
        <label>
            ログインID
            <input type="text" name="login_id" value="<?php echo htmlspecialchars($_SESSION['login_id'] ?? '', ENT_QUOTES); ?>">
            <?php if (isset($errors['login_id'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['login_id'], ENT_QUOTES); ?></p>
            <?php endif; ?>
        </label>
        <br>
        <label>
            パスワード
            <input type="password" name="password"; ?>
            <?php if (isset($errors['password'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['password'], ENT_QUOTES); ?></p>
            <?php endif; ?>
        </label>
        <p><input type="submit" value="ログイン"></p>
        <?php if (isset($errors['db'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($errors['db'], ENT_QUOTES); ?></p>
        <?php endif; ?>
    </form>
</body>
</html>