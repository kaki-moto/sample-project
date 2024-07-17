<?php 
session_start();

// CSRFトークンの検証
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  echo "不正なリクエストです。";
  exit();
}

// トークンの使用後に削除
unset($_SESSION['csrf_token']);

// セッションからフォームデータを取得
$formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];

// セッションデータが存在しない場合
if(empty($formData)){
  echo "フォームデータが存在しません。";
  exit;
}

// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

try {
    // データベースへの接続を確立
    $pdo = new PDO($dsn, $username, $password);
    
    // エラー発生時に例外をスローするように設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // フォームデータを変数にセット
    $family = $formData['family'];
    $first = $formData['first'];
      // フォームデータから性別を整数に変換、整数値として挿入する
      if ($formData['gender'] === '男性') {
        $gender = 1;
      } elseif ($formData['gender'] === '女性') {
        $gender = 2;
      } else {
        $gender = 0; // その他の場合など
      }
    $pref = $formData['pref'];
    $address = $formData['address'];
    $email = $formData['email'];
    // password_hash()を利用してパスワードをハッシュ化し変数$passwordHashに代入、この$passwordHashがデータベースに保存される。 
    $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT); // パスワードのハッシュ化

    // デバッグ出力
    // echo 'Password Hash: ' . $passwordHash;
  
    // ユーザー情報をデータベースに会員情報を挿入する SQLクエリ
    $stmt = $pdo->prepare("INSERT INTO members (name_sei, name_mei, gender, pref_name, address, password, email)
                       VALUES (:name_sei, :name_mei, :gender, :pref_name, :address, :password, :email)");
    // バインドパラメータを設定してクエリを実行
    $stmt->bindParam(':name_sei', $family);
    $stmt->bindParam(':name_mei', $first);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':pref_name', $pref);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':password', $passwordHash);
    $stmt->bindParam(':email', $email);
        
    // クエリを実行して登録完了メッセージを表示
    if ($stmt->execute()) {
        $message = "会員登録が完了しました";
    } else {
        echo "会員登録中にエラーが発生しました。";
    }
    
} catch (PDOException $e) {
    // 接続失敗時のエラーメッセージを表示
    echo '接続失敗: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>会員登録完了画面</title>
    <link rel="stylesheet" href="stylesheet.css">
  </head>

  <body>

  <div class="form-title">
    <h3>会員登録完了<h3>
      
      <div class="form-content">
        <?php echo $message; ?>
      </div>

      <div class="form-content">
        <!--トップに戻るボタン-->
        <form action="top.php" method="get">
          <input type="submit" value="トップに戻る">
        </form>
      </div>
  </div>

</body>

</html>