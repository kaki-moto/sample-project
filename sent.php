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
  $adress = htmlspecialchars($formData['adress'], ENT_QUOTES);
  $email = htmlspecialchars($formData['email'], ENT_QUOTES);
} else {
  // セッションデータがない場合、エラーメッセージを表示するなどの処理
  echo "データが見つかりませんでした。";
  exit();
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
        <?php echo $gender; ?>
      </label>
    </div>
   

    <div class="form-content">
      <label>
        住所
        <?php echo $pref; ?>
        <?php echo $adress; ?>
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
