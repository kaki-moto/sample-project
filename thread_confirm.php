<?php
session_start();

// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


// セッションからフォームデータを取得
$formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];

// セッションデータが存在しない場合
if(empty($formData)){
  echo "フォームデータが存在しません。";
  exit;
}

// 二重送信防ぐためCSRFトークンの作成
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// セッションデータを表示
if (!empty($formData)) {
    // セッションから取得したフォームデータを安全にHTMLに出力するための処理
    $title = htmlspecialchars($formData['title'], ENT_QUOTES);
    $comment = nl2br(htmlspecialchars($formData['comment'], ENT_QUOTES));
  } else {
    // セッションデータが空の場合、エラーメッセージを表示するなどの処理
    echo "データが見つかりませんでした。";
    exit();
  }



?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>スレッド作成フォーム</title>
 <!--二重送信を防ぐためにボタンを押した後に無効化-->
 <script>
      function disableButton() {
        document.getElementById('submitButton').disabled = true;
        document.getElementById('submitButton').value = '処理中...';
      }
 </script>
</head>
<body>

    <div class="thread_confirm">
        <h3>スレッド作成確認画面</h3>

        <form action="thread.php" method="post" onsubmit="disableButton()">

            <label for="title">
                スレッドタイトル
                <!-- リロード後も表示されるように -->
                <!-- セッションからスレッドタイトル取得・表示 -->
                <p><?php echo $title; ?></p>
            </label>
            <br>
            <label for="coment">
                コメント
                <!-- セッションからコメント取得・表示 -->
                <p><?php echo $comment; ?></p>
            </label>
            <br>

            
            <!-- クリックするとDBに登録され、top.phpに -->
            <!--二重送信を防ぐためonsubmit="disableButton()とid="submitButton"を-->
            <!--すでに生成されているCSRFトークンをフォームに含める-->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <p><input type="submit" id="submitButton" value="スレッドを作成する"></p> 
        </form>

        <!--前に戻る-->
        <form action="thread_regist.php" method="get">
            <input type="submit" value="前に戻る">
        </form>
    </div>
</body>
</html>