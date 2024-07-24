<?php 
session_start();

// ログインしてる管理者だけが見れるようにログインチェック
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

// 初期化
$errors = [];

$title = "会員情報登録フォーム";
// $labelId = "登録後に自動採番";
$nextpage = "member_confirm.php";
$editpage = "member_regist.php";

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

// フォームがPOSTメソッドで送信されたらブロック内のコード実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // フォームから送信されたデータ（$POST）全てをセッション変数$SESSION['adminData']に一時保存。
  // セッション変数$SESSIONの'adminData'というキーに格納。
  // adminDataは次ページで使用する為にセッションに保存
  $_SESSION['formData'] = $_POST;

  // バリデーション、エラーがあるかチェック
  // empty()は変数が空であるかどうかを確認するための関数。変数が未定義の場合には警告が出る。
  if (empty($_POST['family'])) {
    $errors['family'] = '※姓を入力してください。';
  // strlen()は文字列の長さを取得する関数、マルチバイト文字を正しく数えるためmb_strlen()に変更
  } elseif (mb_strlen($_POST['family']) > 20) {
    $errors['family'] = '※姓は20文字以内で入力してください。';
  }

  if (empty($_POST['first'])) {
    $errors['first'] = '※名を入力してください。';
  } elseif (mb_strlen($_POST['first']) > 20) {
    $errors['first'] = '※名は20文字以内で入力してください。';
  }

  if (empty($_POST['gender'])) {
    $errors['gender'] = '※性別を選択してください。';
  // value値を男性・女性以外にした時エラーに
  // in_array(検索したい値, 検索対象の配列[array])で送信された値が'男性'または'女性'であるかチェック
  } elseif (!in_array($_POST['gender'], ['男性', '女性'])) {
    // 男性でも女性でもない場合、エラーに
    $errors['gender'] = '※無効な性別が選択されました。';
  }

  // 都道府県が選択されているか
  if (empty($_POST['pref'])) {
    $errors['pref'] = '※都道府県を選択してください。';
  // in_array()で選択された都道府県の値が$prefectures配列に含まれているかチェック
  } elseif (!in_array($_POST['pref'], $prefectures)){
    // 含まれていなければエラーに
    $errors['pref'] = '※無効な都道府県が選択されました。';
  }
  if (strlen($_POST['address']) >100) {
    $errors['address'] = '※住所は100文字以内で入力してください。';
  }

  if (empty($_POST['pass'])) {
    $errors['pass'] = '※パスワードを入力してください。';
  } elseif (strlen($_POST['pass']) < 8 ) {
    $errors['pass'] = '※パスワードは8文字以上で入力してください。';
  } elseif (strlen($_POST['pass']) > 20 ) {
    $errors['pass'] = '※パスワードは20文字以内で入力してください。';
  // preg_match関数はある文字列から正規表現で指定したパターンにマッチした文字列を検索できる
  } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['pass'])){
    $errors['pass'] = '※パスワードは半角英数字で入力してください。';
  }
  if (empty($_POST['pass_con'])) {
    $errors['pass_con'] = '※パスワードを入力してください。';
  } elseif (strlen($_POST['pass_con']) < 8 ) {
    $errors['pass_con'] = '※パスワードは8文字以上で入力してください。';
  } elseif (strlen($_POST['pass_con']) > 20 ) {
    $errors['pass_con'] = '※パスワードは20文字以内で入力してください。';
  } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['pass_con'])){
    $errors['pass_con'] = '※パスワードは半角英数字で入力してください。';
  }

  if ($_POST['pass'] !== $_POST['pass_con']) {
    $errors['pass_con'] = '※パスワードが一致しません。';
  }

  if (empty($_POST['email'])) {
    $errors['email'] = '※メールアドレスを入力してください。';
    } else {
    // メールアドレスの長さチェック
    if (mb_strlen($_POST['email'], 'UTF-8') > 200) {
        $errors['email'] = '※メールアドレスは200文字以内で入力してください。';
    } else {
        // メールアドレスの形式チェック
        $email_pattern = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';
        if (!preg_match($email_pattern, $_POST['email'])) {
            $errors['email'] = '※有効なメールアドレス形式で入力してください。';
        } else {
            // DBに接続して、メールアドレス（login_idカラム）が重複していないかチェック
            // DBに接続できたかできてないかわかるようにするためtry…catch文
            try {
                $pdo = new PDO($dsn, $username, $passwordDb);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE email = :email");
                $stmt->bindParam(':email', $_POST['email']);
                $stmt->execute();
                $emailCount = $stmt->fetchColumn();

                if ($emailCount > 0) {
                    $errors['email'] = '※既に登録されているメールアドレスです。';
                }
            } catch (PDOException $e) {
                $errors['database'] = 'データベースエラー: ' . $e->getMessage();
            }
        }
    }
  }

  // エラー（$errors）がなかったら
  if (empty($errors)) {
      // member_confirm.phpに遷移、シングルクォートではなくダブルクォート
      header("Location: member_confirm.php");
      exit();
  // エラー（$errors）があったら
  } else {
      // $errorsを$_SESSION['errors']に格納
      $_SESSION['errors'] = $errors;
      // 修正のため再びmember_regist.phpへ
      header("Location: member_regist.php");
      exit();
  }
}


// 条件式 ? 真の場合の値 : 偽の場合の値
// isset()で、$_SESSION['adminData']と$_SESSION['errors']の存在をチェック
// 存在する場合はその値を代入、存在しない場合は空の配列[]を変数に代入。
$formData = $_SESSION['formData'] ?? [];
$errors = $_SESSION['errors'] ?? [];


// セッションのクリア、エラーを格納していた変数を削除、エラーメッセージをセッションから削除
unset($_SESSION['errors']);
unset($_SESSION['formData']);

// テンプレの読み込み
require_once("template.php");
?>
