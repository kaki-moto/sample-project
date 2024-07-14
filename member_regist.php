<?php 
session_start();
// 初期化
$errors = [];

// フォームがPOSTメソッドで送信されたらブロック内のコード実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // フォームから送信されたデータ（$POST）をセッション変数$SESSION['formData']に一時保存。
  // セッション変数$SESSIONの'formData'というキーに格納。
  // フォームデータを次ページsent.phpで使用する為に一時保存
  $_SESSION['formData'] = $_POST;

  // バリデーション、エラーがあるかチェック
  // empty()は変数が空であるかどうかを確認するための関数。変数が未定義の場合には警告が出る。
  if (empty($_POST['family'])) {
    $errors['family'] = '姓を入力してください。';
  // strlen()は文字列の長さを取得する関数
  } elseif (strlen($_POST['family']) > 20) {
    $errors['family'] = '姓は20文字以内で入力してください。';
  }

  if (empty($_POST['first'])) {
    $errors['first'] = '名を入力してください。';
  } elseif (strlen($_POST['first']) > 20) {
    $errors['first'] = '名は20文字以内で入力してください。';
  }

  // エラー（$errors）がなかったら
  if (empty($errors)) {
      // sent.phpに遷移
      header('Location: sent.php');
      exit();
  // エラー（$errors）があったら
  } else {
      // $errorsを$_SESSION['errors']に格納
      $_SESSION['errors'] = $errors;
      // 修正のため再びmember_regist.phpへ
      header('Location: member_regist.php');
      exit();
  }
}

// 条件式 ? 真の場合の値 : 偽の場合の値
// isset()で、$_SESSION['formData']と$_SESSION['errors']の存在をチェック
// 存在する場合はその値を代入、存在しない場合は空の配列[]を変数に代入。
$formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];

// セッションのクリア、エラーを格納していた変数を削除、エラーメッセージをセッションから削除
unset($_SESSION['errors']);

?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>会員登録フォーム</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <script type="text/javascript">
        window.onload = function() {
            // パスワードを表示しないようにする
            document.getElementById('pass').value = '';
            document.getElementById('pass_con').value = '';
        };
    </script>
  </head>

  <body>
    <div class="signup-form">
        <h3>会員情報登録フォーム</h3>
    <!-- "member_regist.php"でバリデーション? -->
    <form id="form" action="member_regist.php" method="post">
        
        <label>
          氏名
          <label>
            姓
            <!-- ENT_QUOTESはhtmlspecialchars関数と一緒に使われる定数。'と"をHTMLエンティティに変換、これにより、HTMLの特殊文字がそのまま表示されるのを防ぐ。 -->
            <input type="text" name="family" maxlength="20" required value="<?php echo htmlspecialchars($formData['family'] ?? '', ENT_QUOTES); ?>">
            <!-- もしfamilyにエラーが存在したら -->
            <?php if (isset($errors['family'])): ?>
              <!-- 赤色の文字で htmlspecialchars($errors['family'], ENT_QUOTES) を出力？-->
              <p style="color: red;"><?php echo htmlspecialchars($errors['family'], ENT_QUOTES); ?></p>
            <?php endif; ?>
          </label>
          <label>
            名
            <input type="text" name="first" maxlength="20" required value="<?php echo htmlspecialchars($formData['first'] ?? '', ENT_QUOTES); ?>">
            </label>
        </label>

        <br>

        <label>
          性別
          <input type="radio" name="gender" value="男性" required <?php if (isset($formData['gender']) && $formData['gender'] === '男性') echo 'checked'; ?>>男性
          <input type="radio" name="gender" value="女性" required <?php if (isset($formData['gender']) && $formData['gender'] === '女性') echo 'checked'; ?>>女性
        </label>

        <br>

        <label>住所
          <label>
            都道府県
            <imput type=“select” required>
              <select name="pref">
                <option value="" selected>選択してください</option>
                <option value="北海道">北海道</option>
                <option value="青森県">青森県</option>
                <option value="岩手県">岩手県</option>
                <option value="宮城県">宮城県</option>
                <option value="秋田県">秋田県</option>
                <option value="山形県">山形県</option>
                <option value="福島県">福島県</option>
                <option value="茨城県">茨城県</option>
                <option value="栃木県">栃木県</option>
                <option value="群馬県">群馬県</option>
                <option value="埼玉県">埼玉県</option>
                <option value="千葉県">千葉県</option>
                <option value="東京都">東京都</option>
                <option value="神奈川県">神奈川県</option>
                <option value="新潟県">新潟県</option>
                <option value="富山県">富山県</option>
                <option value="石川県">石川県</option>
                <option value="福井県">福井県</option>
                <option value="山梨県">山梨県</option>
                <option value="長野県">長野県</option>
                <option value="岐阜県">岐阜県</option>
                <option value="静岡県">静岡県</option>
                <option value="愛知県">愛知県</option>
                <option value="三重県">三重県</option>
                <option value="滋賀県">滋賀県</option>
                <option value="京都府">京都府</option>
                <option value="大阪府">大阪府</option>
                <option value="兵庫県">兵庫県</option>
                <option value="奈良県">奈良県</option>
                <option value="和歌山県">和歌山県</option>
                <option value="鳥取県">鳥取県</option>
                <option value="島根県">島根県</option>
                <option value="岡山県">岡山県</option>
                <option value="広島県">広島県</option>
                <option value="山口県">山口県</option>
                <option value="徳島県">徳島県</option>
                <option value="香川県">香川県</option>
                <option value="愛媛県">愛媛県</option>
                <option value="高知県">高知県</option>
                <option value="福岡県">福岡県</option>
                <option value="佐賀県">佐賀県</option>
                <option value="長崎県">長崎県</option>
                <option value="熊本県">熊本県</option>
                <option value="大分県">大分県</option>
                <option value="宮崎県">宮崎県</option>
                <option value="鹿児島県">鹿児島県</option>
                <option value="沖縄県">沖縄県</option>
            </select>
          </label>
          <label>
          それ以降の住所
          <input type="text" name="adress" maxlength="100" value="<?php echo htmlspecialchars($formData['adress'] ?? '', ENT_QUOTES); ?>">
          </label>
        </label>

        <br>

        <label>
          パスワード
          <input type="password" name="pass" pattern="^[a-zA-Z0-9]+$" minlength="8" maxlength="20" required>
        </label>

        <br>

        <label>
          パスワードの確認
          <input type="password" name="pass_con" pattern="^[a-zA-Z0-9]+$" minlength="8" maxlength="20" required>
        </label>

        <br>

        <label>
          メールアドレス
          <input type="text" name="email" maxlength="200" required value="<?php echo htmlspecialchars($formData['email'] ?? '', ENT_QUOTES); ?>">
        </label>
        
        <p><input type="submit" value="確認画面へ"></p>

    </form>
    </div>

  </body>

</html>


