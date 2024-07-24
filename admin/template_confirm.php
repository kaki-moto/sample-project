<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="stylesheet.css">
    <!--二重送信を防ぐためにボタンを押した後に無効化-->
    <script>
      function disableButton() {
        document.getElementById('submitButton').disabled = true;
        document.getElementById('submitButton').value = '処理中...';
      }
    </script>
  </head>

  <body>

    <div class="form-title">
      <h3><?php echo $title; ?></h3>

      <label>
            ID <?php echo $labelId; ?>
      </label>

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
          // フォームデータから性別を整数に変換、整数値として挿入する
          if ($formData['gender'] === '男性') {
            $gender = 1;
          } elseif ($formData['gender'] === '女性') {
            $gender = 2;
          } else {
            $gender = 0; // その他の場合など
          }
          ?>
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
    
      <!--二重送信を防ぐためonsubmit="disableButton()とid="submitButton"を-->
      <form action="member_confirm.php" method="post" onsubmit="disableButton()">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="submit" id="submitButton" value="登録完了">
      </form>
        <button type="button" onclick=history.back()>前に戻る</button>
    </div>

</body>

</html>
