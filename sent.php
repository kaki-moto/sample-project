<?php 
session_start();
// セッションからフォームデータを取得
$formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];

// セッションデータが存在しない場合
if(empty($formData)){
  echo "フォームデータが存在しません。";
  exit;
}

// セッションデータを表示
// $formDataが空でない時に実行
if (!empty($formData)) {
  // セッションから取得したフォームデータを安全にHTMLに出力するための処理
  $family = htmlspecialchars($formData['family'], ENT_QUOTES);
  $first = htmlspecialchars($formData['first'], ENT_QUOTES);
  $gender = htmlspecialchars($formData['gender'], ENT_QUOTES);
  $pref = htmlspecialchars($formData['pref'], ENT_QUOTES);
  $address = htmlspecialchars($formData['address'], ENT_QUOTES);
  $email = htmlspecialchars($formData['email'], ENT_QUOTES);
} else {
  // セッションデータがない場合、エラーメッセージを表示するなどの処理
  echo "データが見つかりませんでした。";
  exit();
}

?>


<?php 
session_start();
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
    $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT); // パスワードのハッシュ化
  
    // データベースに会員情報を挿入するSQLクエリ
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
        echo "会員登録が完了しました。";
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
    <title>会員登録画面フォーム</title>
    <link rel="stylesheet" href="stylesheet.css">
  </head>

  <body>

    <div class="form-title">会員情報確認画面</div>

    <div class="form-content">
      <label>
        氏名
        <?php echo $family; ?>
        <?php echo $first; ?>
      </label>
    </div>

    <div class="form-content">
      <label>
        性別
        <?php 
        // 性別の整数値を文字列に変換して表示
        if ($gender === 1) {
          echo '男性';
        } elseif ($gender === 2) {
            echo '女性';
        } else {
            echo 'その他'; // もし他の性別があればその処理も追加
        }
        ?>
      </label>
    </div>
   

    <div class="form-content">
      <label>
        住所
        <?php echo $pref; ?>
        <?php echo $address; ?>
      </label>
    </div>
    

    <div class="form-content">
      <label>
        パスワード
        <?php echo 'セキュリティのため非表示'; ?>
      </label>
    </div>

    <div class="form-content">
      <label>
        メールアドレス
        <?php echo $email; ?>
      </label>
    </div>

    <form action="regist_comp.php" method="post">
      <input type="submit" value="登録完了">
    </form>
    <button type="button" onclick=history.back()>前に戻る</button>

</body>

</html>
