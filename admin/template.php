<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <script type="text/javascript">
        window.onload = function() {
            // リロードされた時パスワードを表示しないようにする
            document.getElementById('pass').value = '';
            document.getElementById('pass_con').value = '';
        };
    </script>
  </head>

  <body>
    <header>
        <h3><?php echo $title; ?></h3>
    </header>

    <!-- 「確認画面へ」押したら登録の際は"member_regist.php"、編集の際は"member_edit.php?id=$id" でバリデーション -->
    <form id="form" action="<?php echo $editpage; ?>" method="POST">

        <label>
            ID <?php echo htmlspecialchars($formData['id'] ?? '', ENT_QUOTES); ?>
        </label>
        
        <br>

        <label>
          氏名
          <label>
            姓
            <!-- ENT_QUOTESはhtmlspecialchars関数と一緒に使われる定数。'と"をHTMLエンティティに変換、これにより、HTMLの特殊文字がそのまま表示されるのを防ぐ。 -->
            <input type="text" name="family" value="<?php echo htmlspecialchars($formData['name_sei'] ?? '', ENT_QUOTES); ?>">
            <!-- もしfamilyにエラーが存在したら -->
            <?php if (isset($errors['family'])): ?>
              <!-- 赤色の文字で htmlspecialchars($errors['family'], ENT_QUOTES) を出力？-->
              <p style="color: red;"><?php echo htmlspecialchars($errors['family'], ENT_QUOTES); ?></p>
            <?php endif; ?>
          </label>
          <label>
            名
            <input type="text" name="first" value="<?php echo htmlspecialchars($formData['name_mei'] ?? '', ENT_QUOTES); ?>">
            <?php if (isset($errors['first'])): ?>
              <p style="color: red;"><?php echo htmlspecialchars($errors['first'], ENT_QUOTES); ?></p>
            <?php endif; ?>
          </label>
        </label>

        <br>

        <label>
          性別
          <input type="radio" name="gender" value="男性" <?php if (isset($formData['gender']) && $formData['gender'] === '1') echo 'checked'; ?>>男性
          <input type="radio" name="gender" value="女性" <?php if (isset($formData['gender']) && $formData['gender'] === '2') echo 'checked'; ?>>女性
          <?php if (isset($errors['gender'])): ?>
            <p style="color: red;"><?php echo $errors['gender']; ?></p>
          <?php endif; ?>
        </label>

        <br>

        <label>
          住所
          <label>
            都道府県
            <select name="pref">
              <!-- 都道府県の選択結果が保持されるように -->
              <option value="" <?php echo !isset($formData['pref_name']) || $formData['pref_name'] === '' ? 'selected' : ''; ?>>選択してください</option>
                <?php foreach ($prefectures as $prefecture): ?>
                <option value="<?php echo htmlspecialchars($prefecture, ENT_QUOTES); ?>"
                <?php echo isset($formData['pref_name']) && $formData['pref_name'] === $prefecture ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($prefecture, ENT_QUOTES); ?>
              </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['pref'])): ?>
              <p style="color: red;"><?php echo $errors['pref']; ?></p>
            <?php endif; ?>
          </label>
          <label>
            それ以降の住所
            <input type="text" name="address" value="<?php echo htmlspecialchars($formData['address'] ?? '', ENT_QUOTES); ?>">
            <?php if (isset($errors['address'])): ?>
              <p style="color: red;"><?php echo $errors['address']; ?></p>
            <?php endif; ?>
          </label>
        </label>

        <br>

        <label>
          パスワード
          <input type="password" name="pass" value="">
          <?php if (isset($errors['pass'])): ?>
            <p style="color: red;"><?php echo $errors['pass']; ?></p>
          <?php endif; ?>
        </label>

        <br>

        <label>
          パスワードの確認
          <input type="password" name="pass_con" value="">
          <?php if (isset($errors['pass_con'])): ?>
            <p style="color: red;"><?php echo $errors['pass_con']; ?></p>
          <?php endif; ?>
        </label>

        <br>

        <label>
          メールアドレス
          <input type="text" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? '', ENT_QUOTES); ?>">
          <?php if (isset($errors['email'])): ?>
            <p style="color: red;"><?php echo $errors['email']; ?></p>
          <?php endif; ?>
        </label>
        
        <p><input type="submit" value="確認画面へ"></p>
        
    </form>

    <form action="admin_top.php" method="get">
      <input type="submit" value="トップに戻る">
    </form>
    </div>

  </body>

</html>


