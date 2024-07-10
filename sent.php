<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>会員登録画面フォーム</title>
    <link rel="stylesheet" href="stylesheet.css">
  </head>

  <body>

  <div class="form-title">会員情報確認画面</div>
    <div class="form-content">氏名</div>
    <?php echo $_POST['family']; ?>
    <?php echo $_POST['first']; ?>

    <div class="form-content">性別</div>
    <?php echo $_POST['radio']; ?>

    <div class="form-content">住所</div>
    <?php echo $_POST['pref']; ?>
    <?php echo $_POST['adress']; ?>

    <div class="form-content">パスワード</div>
    <?php echo 'セキュリティのため非表示'; ?>

    <div class="form-content">メールアドレス</div>
    <?php echo $_POST['mail']; ?>

    <input type="submit" value="登録完了">
    <button type="button" onclick=history.back()>前に戻る</button>

</body>

</html>
