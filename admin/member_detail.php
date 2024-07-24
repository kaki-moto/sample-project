<?php
session_start();

// 管理者用member.phpはログインしてる管理者だけが見れるように
// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// URLパラメータからIDを取得
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

try {
    // DBへの接続を確立
    $pdo = new PDO($dsn, $username, $password);
    // エラー発生時に例外をスローするように設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // DBのmemberテーブルから会員情報を取得
    $query = "SELECT id, name_sei, name_mei, gender, pref_name, address, email
              FROM members 
              WHERE id = :id AND deleted_at IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        throw new Exception('会員が見つかりません。');
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>会員詳細画面</title>
    <link rel="stylesheet" href="stylesheet.css">
    <style>
      header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 10px;
      }
      h3 {
          margin: 0;
      }
    </style>
  </head>

  <body>
    <header>
        <h3>会員詳細</h3>
        <form action="member.php" method="get">
          <input type="submit" value="一覧に戻る">
        </form>
    </header>
    <main>

        <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>

        <label>
            ID <?php echo htmlspecialchars($member['id']); ?>
        </label>
        <br>
        <label>
          氏名 <?php echo htmlspecialchars($member['name_sei'] . ' ' . $member['name_mei']); ?>
        </label>
        <br>
        <label>
          性別 <?php echo ($member['gender'] == 1) ? '男性' : '女性'; ?>
        </label>
        <br>
        <label>
          住所  <?php echo htmlspecialchars($member['pref_name'] . $member['address']); ?>
        </label>
        <br>
        <label>
          パスワード
          <?php echo 'セキュリティのため非表示'; ?>
        </label>
        <br>
        <label>
          メールアドレス <?php echo htmlspecialchars($member['email']); ?>
        </label>

        <form action="member_edit.php" method="get">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($member['id']); ?>">
            <input type="submit" value="編集">
        </form>
        <form action="member_delete.php" method="get">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($member['id']); ?>">
            <input type="submit" value="削除">
        </form>
        <?php endif; ?>

    </main>
  </body>
</html>