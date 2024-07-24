<?php
session_start();
date_default_timezone_set('Asia/Tokyo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();

// セッションからエラーとフォームデータを取得
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['formData'] ?? [];
unset($_SESSION['errors'], $_SESSION['formData']);

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$title = "会員編集";
$id = htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES);
$nextpage = "edit_confirm.php";
$editpage = "member_edit.php?id=$id";

$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$passwordDb = 'K4aCuFEh';

$prefectures = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
    '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
    '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
    '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
    '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
];

// PDO接続を関数の外で行う
try {
    $pdo = new PDO($dsn, $username, $passwordDb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $errors['database'] = 'データベース接続エラー: ' . $e->getMessage();
}

// ユーザーデータ取得
if (empty($formData)) {
    try {
        $dbData = getUserData($pdo, $id);
        if ($dbData) {
            $formData = [
                'id' => $dbData['id'],
                'family' => $dbData['name_sei'],
                'first' => $dbData['name_mei'],
                'gender' => ($dbData['gender'] == 1) ? '男性' : '女性',
                'pref' => $dbData['pref_name'],
                'address' => $dbData['address'],
                'email' => $dbData['email']
            ];
            $labelId = htmlspecialchars($dbData['id'] ?? '', ENT_QUOTES);
        }
    } catch (PDOException $e) {
        $errors['database'] = 'データベースエラー: ' . $e->getMessage();
    }
}

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'id' => $id,
        'family' => $_POST['family'] ?? '',
        'first' => $_POST['first'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'pref' => $_POST['pref'] ?? '',
        'address' => $_POST['address'] ?? '',
        'email' => $_POST['email'] ?? '',
        'pass' => $_POST['pass'] ?? '',
        'pass_con' => $_POST['pass_con'] ?? ''
    ];
    $errors = validateForm($formData, $pdo, $prefectures);

    if (empty($errors)) {
        $_SESSION['formData'] = $formData;
        header("Location: edit_confirm.php");
        exit();
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['formData'] = $formData;
        header("Location: member_edit.php?id=$id");
        exit();
    }
}

// エラーがある場合、セッションからフォームデータを取得
if (!empty($errors)) {
    $formData = $_SESSION['formData'] ?? $formData;
}

// テンプレートの読み込み
require_once("template.php");

ob_end_flush();

// 関数定義
function getUserData($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validateForm($data, $pdo, $prefectures) {
    $errors = [];

    if (empty($data['family'])) {
        $errors['family'] = '※姓を入力してください。';
    } elseif (mb_strlen($data['family']) > 20) {
        $errors['family'] = '※姓は20文字以内で入力してください。';
    }

    if (empty($data['first'])) {
        $errors['first'] = '※名を入力してください。';
    } elseif (mb_strlen($data['first']) > 20) {
        $errors['first'] = '※名は20文字以内で入力してください。';
    }

    if (empty($data['gender'])) {
        $errors['gender'] = '※性別を選択してください。';
    } elseif (!in_array($data['gender'], ['男性', '女性'])) {
        $errors['gender'] = '※無効な性別が選択されました。';
    }

    if (empty($data['pref'])) {
        $errors['pref'] = '※都道府県を選択してください。';
    } elseif (!in_array($data['pref'], $prefectures)) {
        $errors['pref'] = '※無効な都道府県が選択されました。';
    }

    if (strlen($data['address']) > 100) {
        $errors['address'] = '※住所は100文字以内で入力してください。';
    }

    // パスワードのバリデーションを条件付きに変更
    if (!empty($data['pass'])) {
        if (strlen($data['pass']) < 8) {
            $errors['pass'] = '※パスワードは8文字以上で入力してください。';
        } elseif (strlen($data['pass']) > 20) {
            $errors['pass'] = '※パスワードは20文字以内で入力してください。';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $data['pass'])) {
            $errors['pass'] = '※パスワードは半角英数字で入力してください。';
        }

        if (empty($data['pass_con'])) {
            $errors['pass_con'] = '※確認用パスワードを入力してください。';
        } elseif ($data['pass'] !== $data['pass_con']) {
            $errors['pass_con'] = '※パスワードが一致しません。';
        }
    }

    if (empty($data['email'])) {
        $errors['email'] = '※メールアドレスを入力してください。';
    } elseif (mb_strlen($data['email'], 'UTF-8') > 200) {
        $errors['email'] = '※メールアドレスは200文字以内で入力してください。';
    } else {
        $email_pattern = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';
        if (!preg_match($email_pattern, $data['email'])) {
            $errors['email'] = '※有効なメールアドレス形式で入力してください。';
        } else {
            try {
                // 現在のユーザーのメールアドレスを取得
                $stmt = $pdo->prepare("SELECT email FROM members WHERE id = :id");
                $stmt->bindParam(':id', $data['id']);
                $stmt->execute();
                $currentEmail = $stmt->fetchColumn();

                // メールアドレスが変更されている場合のみ重複チェックを行う
                if ($data['email'] !== $currentEmail) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE email = :email AND id != :id");
                    $stmt->bindParam(':email', $data['email']);
                    $stmt->bindParam(':id', $data['id']);
                    $stmt->execute();
                    $emailCount = $stmt->fetchColumn();

                    if ($emailCount > 0) {
                        $errors['email'] = '※既に登録されているメールアドレスです。';
                    }
                }
            } catch (PDOException $e) {
                $errors['database'] = 'データベースエラー: ' . $e->getMessage();
            }
        }
    }
    return $errors;
}
?>