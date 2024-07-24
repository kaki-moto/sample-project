<?php
session_start();
date_default_timezone_set('Asia/Tokyo');

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// URLパラメータからIDを取得
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    $_SESSION['error_message'] = "無効なIDです。";
    header('Location: member.php');
    exit();
}

$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // トランザクション開始
    $pdo->beginTransaction();

    // 会員が存在するか確認
    $stmt = $pdo->prepare("SELECT id FROM members WHERE id = :id AND deleted_at IS NULL");
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        throw new Exception("指定された会員が見つかりません。");
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE members SET deleted_at = :deleted_at WHERE id = :id");
    $stmt->execute([
        ':deleted_at' => $now,
        ':id' => $id
    ]);

    // トランザクションをコミット
    $pdo->commit();

    // 削除成功のメッセージをセッションに保存
    $_SESSION['delete_success'] = "ID: {$id} の会員を削除しました。";

} catch (Exception $e) {
    // エラーが発生した場合はロールバック
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error_message'] = "削除中にエラーが発生しました: " . $e->getMessage();
}

// member.phpにリダイレクト
header('Location: member.php');
exit;