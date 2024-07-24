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
          echo ($gender === '男性') ? '男性' : '女性';
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
      <form action="edit_confirm.php" method="post" onsubmit="disableButton()">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <!--$compButtonは登録なら登録完了、編集なら編集完了ボタンに-->
        <input type="submit" id="submitButton" value="<?php echo $compButton; ?>">
      </form>
        <button type="button" onclick="history.back()">前に戻る</button>
    </div>
  </body>
</html>