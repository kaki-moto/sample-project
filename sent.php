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
          <label>
            姓
            <?php echo $_POST['family']; ?>
          </label>
          <label>
            名
            <?php echo $_POST['first']; ?>
          </label>
      </label>
    </div>

    <div class="form-content">
      <label>
        性別
        <?php echo $_POST['radio']; ?>
      </label>
    </div>
   

    <div class="form-content">
      <label>
        住所
        <?php echo $_POST['pref']; ?>
        <?php echo $_POST['adress']; ?>
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
        <?php echo $_POST['email']; ?>
      </label>
    </div>

    <form action="regist_comp.php" method="post">
      <input type="submit" value="登録完了">
    </form>
    <button type="button" onclick=history.back()>前に戻る</button>

</body>

</html>
