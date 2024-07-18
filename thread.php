<?php
session_start();

// まず、ログインしている状態か確認。ログイン状態でしか開けないようにしたい。
//　ログインできてない場合
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// CSRFトークンの検証
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "不正なリクエストです。";
    exit();
  }
  
  // トークンの使用後に削除
  unset($_SESSION['csrf_token']);

// セッションが存在するか確認。存在すればその値を変数$formDataに代入。存在しなければ空の配列[]を代入。
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
    // DBのmemberテーブルのidどうやって取得するの
    $id = $_SESSION['user_id'] ; // DBのmemberテーブルから会員ID（id）を取得し、それをthreadsテーブルのmember_idに挿入。
    $title = $formData['title'];
    $comment = $formData['comment'];
    
    // ユーザー情報をデータベースに会員情報を挿入する SQLクエリ
    $stmt = $pdo->prepare("INSERT INTO threads (member_id, title, content)
                       VALUES (:member_id, :title, :content)");
    // バインドパラメータを設定してクエリを実行
    $stmt->bindParam(':member_id', $id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $comment);

    // クエリを実行してページ遷移
    if ($stmt->execute()) {
        header('Location: top.php');;
    } else {
        echo "スレッド作成中にエラーが発生しました。";
    }

} catch (PDOException $e) {
    // 接続失敗時のエラーメッセージを表示
    echo '接続失敗: ' . $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>スレッド一覧</title>
</head>
<body>
    <div>
        
    </div>
</body>
</html>


