<?php 
session_start();
date_default_timezone_set('Asia/Tokyo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッションからフォームデータを取得
$formData = $_SESSION['formData'] ?? [];

// セッションデータが存在しない場合
if(empty($formData)){
  echo "フォームデータが存在しません。";
  exit;
}

$title = "会員情報編集確認画面";
$compButton = "編集完了";

// 二重送信防ぐためCSRFトークンの作成
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

// フォームデータをテンプレート用に準備
$labelId = htmlspecialchars($formData['id'] ?? '', ENT_QUOTES);
$family = htmlspecialchars($formData['family'] ?? '', ENT_QUOTES);
$first = htmlspecialchars($formData['first'] ?? '', ENT_QUOTES);
$gender = $formData['gender'] ?? '';
$pref = htmlspecialchars($formData['pref'] ?? '', ENT_QUOTES);
$address = htmlspecialchars($formData['address'] ?? '', ENT_QUOTES);
$email = htmlspecialchars($formData['email'] ?? '', ENT_QUOTES);

// このページで編集完了ボタンを押すとここに。POSTメソッドで送信されたものを受け取る。
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証（二重送信の防止）
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "不正なリクエストです。";
        exit();
    }
    // トークンの使用後に削除（二重送信の防止）
    unset($_SESSION['csrf_token']);

    $dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
    $username = 'root';
    $password = 'K4aCuFEh';

    try {
        // データベースへの接続を確立
        $pdo = new PDO($dsn, $username, $password);
        // エラー発生時に例外をスローするように設定
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // フォームデータを変数にセット
        $id = $formData['id'];
        $family = $formData['family'];
        $first = $formData['first'];
        $gender = ($formData['gender'] === '男性') ? 1 : 2;
        $pref = $formData['pref'];
        $address = $formData['address'];
        $email = $formData['email'];
        $updatedAt = date('Y-m-d H:i:s');

        // SQLクエリの基本部分
        $sql = "UPDATE members SET name_sei = :name_sei, name_mei = :name_mei, 
                gender = :gender, pref_name = :pref_name, address = :address, 
                email = :email, updated_at = :updated_at";

        // パスワードが入力されている場合のみ、パスワード更新を追加
        $params = [
            ':id' => $id,
            ':name_sei' => $family,
            ':name_mei' => $first,
            ':gender' => $gender,
            ':pref_name' => $pref,
            ':address' => $address,
            ':email' => $email,
            ':updated_at' => $updatedAt
        ];

        if (!empty($formData['pass'])) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($formData['pass'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        // ユーザーの会員情報を更新するSQLクエリを準備
        $stmt = $pdo->prepare($sql);

        // クエリを実行して更新完了すればmember.phpへ遷移
        if ($stmt->execute($params)) {
            header('Location: member.php');
            exit();
        } else {
            echo "会員情報の更新中にエラーが発生しました。";
        }

    } catch (PDOException $e) {
        // 接続失敗時のエラーメッセージを表示
        echo '接続失敗: ' . $e->getMessage();
    }
}

require_once("template_confirm.php");
?>