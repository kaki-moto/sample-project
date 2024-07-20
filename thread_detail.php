<?php
session_start();
date_default_timezone_set('Asia/Tokyo'); //日本時間に

// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

// ログイン状態チェック
$loggedIn = isset($_SESSION['user_id']);

// エラー配列の初期化
$errors = [];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // スレッドIDを取得
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("無効なスレッドIDです。");
    }
    $thread_id = intval($_GET['id']);

    // ページ番号を取得
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 5; // 1ページに表示するコメントの数
    $offset = ($page - 1) * $limit; // DBのどの行からデータを取得するか。$pageが1なら$offsetは0で、$pageが2なら$offsetは5で5行目からデータ取得。

    // 指定されたスレッドIDに対応するスレッド情報を取得。そのスレッドを作成したユーザーの名前を取得。
    $stmt = $pdo->prepare("SELECT threads.*, CONCAT(members.name_sei, ' ', members.name_mei) AS member_name FROM threads JOIN members ON threads.member_id = members.id WHERE threads.id = :id");
    $stmt->bindParam(':id', $thread_id, PDO::PARAM_INT);
    $stmt->execute();
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        throw new Exception("スレッドが見つかりません。");
    }

    // 総reaction数を取得
    $stmt = $pdo->prepare("SELECT COUNT(*) as reaction_count FROM comments WHERE thread_id = :thread_id");
    $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
    $stmt->execute();
    $reaction_count = $stmt->fetchColumn();

    // コメント情報を取得。指定されたIDに対応するコメント情報をDBから取得。そのコメントを投稿したユーザの名前を結合して取得。
    $stmt = $pdo->prepare("SELECT comments.*, CONCAT(members.name_sei, ' ', members.name_mei) AS member_name FROM comments JOIN members ON comments.member_id = members.id WHERE comments.thread_id = :thread_id ORDER BY comments.created_at ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // POSTリクエスト（コメント(reaction)のバリデーション）の処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['reaction'])) {
            $errors['reaction'] = '※コメントを入力してください。';
        } elseif (mb_strlen($_POST['reaction']) > 500) {
            $errors['reaction'] = '※コメントは500文字以内で入力してください。';
        }

        if (empty($errors)) {
            $now = date('Y-m-d H:i:s');
            // コメントをデータベースcommentsに保存する処理
            $stmt = $pdo->prepare("INSERT INTO comments (member_id, thread_id, comment, created_at, updated_at) VALUES (:member_id, :thread_id, :comment, :created_at, :updated_at)");
            $stmt->execute([
                ':member_id' => $_SESSION['user_id'],
                ':thread_id' => $thread_id,
                ':comment' => $_POST['reaction'],
                ':created_at' => $now,
                ':updated_at' => $now
            ]);

            // 保存成功後、同じページにリダイレクト
            header("Location: thread_detail.php?id=$thread_id&page=$page");
            exit();
        }
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

$total_pages = ceil($reaction_count / $limit);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>スレッド詳細</title>
    <style>
    .pagination a {
        text-decoration: none;
        color: black;
        margin: 0 5px;
    }
    .pagination a.disabled {
        color: grey;
        pointer-events: none;
    }
</style>
</head>
<body>
    <header>
        <a href="thread.php">スレッド一覧にもどる</a>
    </header>
    <main>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php else: ?>
            <h3><?php echo htmlspecialchars($thread['title']); ?></h3>
            <br>
            <?php echo $reaction_count; ?>コメント
            <?php echo htmlspecialchars($thread['created_at']); ?>
            <br>
            <!-- ページネーション -->
            <div class="pagination">
                <!-- $page <= 1 の場合、つまり現在のページが1ページ目かそれ以前の場合、リンクに 'disabled' クラスが追加 -->
                <a class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page - 1; ?>">前へ&gt;</a>
                <!-- $page >= $total_pagesの場合、つまり現在のページが最後のページかそれ以降の場合、リンクに 'disabled' クラスが追加 -->
                <a class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page + 1; ?>">次へ&gt;</a>
            </div>
            
            <!-- 投稿者氏名、投稿日時、コメントを表示 -->
            投稿者：<?php echo htmlspecialchars($thread['member_name']); ?>
            <?php echo htmlspecialchars($thread['created_at']); ?>
            <p><?php echo htmlspecialchars($thread['content']); ?></p>

            <!-- コメントID、コメント主氏名、投稿日時、コメントを表示 -->
            <?php if (!empty($comments)): ?>
                <ul style="list-style-type: none;">
                    <?php foreach ($comments as $comment): ?>
                        <li>
                            <?php echo htmlspecialchars($comment['id']); ?>.
                            <?php echo htmlspecialchars($comment['member_name']); ?>
                            <?php echo htmlspecialchars($comment['created_at']); ?>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </li>
                    <?php endforeach; ?>       
                </ul>
                <!-- ページネーション -->
                <div class="pagination">
                <a class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page - 1; ?>">前へ&gt;</a>
                <a class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page + 1; ?>">次へ&gt;</a>
                </div>
            <?php else: ?>
                <p>コメントがありません。</p>
            <?php endif; ?>

            <?php if($loggedIn): ?>
                <form action="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page; ?>" method="POST">
                    <textarea name="reaction"></textarea>
                    <?php if (isset($errors['reaction'])): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($errors['reaction']); ?></p>
                    <?php endif; ?>
                    <p><input type="submit" value="コメントする"></p>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>