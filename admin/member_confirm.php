<?php 
session_start();
date_default_timezone_set('Asia/Tokyo');

$title = "会員登録画面フォーム";
$labelId = "登録後に自動採番";
$compButton = "登録完了";

// セッションからフォームデータを取得
$formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];

// セッションデータが存在しない場合
if(empty($formData)){
  echo "フォームデータが存在しません。";
  exit;
}

// 二重送信防ぐためCSRFトークンの作成
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

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



// このページで登録完了ボタンを押すとここに。POSTメソッドで送信されたものを受け取る。
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRFトークンの検証（二重送信の防止）
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "不正なリクエストです。";
    exit();
  }
  // トークンの使用後に削除（二重送信の防止）
  unset($_SESSION['csrf_token']);

  // セッションからフォームデータを取得
  $formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : []; 
  // セッションデータが存在しない場合
  if(empty($formData)){
    echo "フォームデータが存在しません。";
    exit;
  }

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
    $passwordHash = password_hash($formData['pass'], PASSWORD_DEFAULT); // パスワードのハッシュ化
    // 現在の日時を取得した上で$createdAtと$updatedAtの変数を設定
    $createdAt = date( 'Y-m-d H:i:s');
    $updatedAt = date( 'Y-m-d H:i:s');

    // ユーザーの会員情報をデータベースに挿入する SQLクエリ
    $stmt = $pdo->prepare("INSERT INTO members (name_sei, name_mei, gender, pref_name, address, password, email, created_at, updated_at)
                      VALUES (:name_sei, :name_mei, :gender, :pref_name, :address, :password, :email, :created_at, :updated_at)");
    // バインドパラメータを設定してクエリを実行
    $stmt->bindParam(':name_sei', $family);
    $stmt->bindParam(':name_mei', $first);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':pref_name', $pref);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':password', $passwordHash);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':created_at', $createdAt);
    $stmt->bindParam(':updated_at', $updatedAt);
    
    // クエリを実行して登録完了すればmember.phpへ遷移
    if ($stmt->execute()) {
        header('Location: member.php');
    } else {
        echo "会員登録中にエラーが発生しました。";
    }

  } catch (PDOException $e) {
  // 接続失敗時のエラーメッセージを表示
  echo '接続失敗: ' . $e->getMessage();
  }



}

require_once("template_confirm.php");

?>

