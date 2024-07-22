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
    
        // いいね処理
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_comment_id'])) {
            $comment_id = $_POST['like_comment_id'];
            $member_id = $_SESSION['user_id'];  
        
            // いいねの存在確認
            $stmt = $pdo->prepare("SELECT * FROM likes WHERE member_id = ? AND comment_id = ?");
            $stmt->execute([$member_id, $comment_id]);
            $existing_like = $stmt->fetch();
        
            if ($existing_like) {
                // いいねを削除
                $stmt = $pdo->prepare("DELETE FROM likes WHERE member_id = ? AND comment_id = ?");
                $stmt->execute([$member_id, $comment_id]);
                $liked = false;
            } else {
                // いいねを追加
                $stmt = $pdo->prepare("INSERT INTO likes (member_id, comment_id) VALUES (?, ?)");
                $stmt->execute([$member_id, $comment_id]);
                $liked = true;
            }
    
            // いいねの総数を取得
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE comment_id = ?");
            $stmt->execute([$comment_id]);
            $like_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
            // Ajax リクエストの場合は JSON を返す
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true, 'liked' => $liked, 'likeCount' => $like_count]);
                exit;
            } else {
                // 通常のPOSTリクエストの場合はリダイレクト
                header("Location: thread_detail.php?id=$thread_id&page=$page");
                exit;
            }
        }
    

    // スレッドIDを取得
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("無効なスレッドIDです。");
    }
    $thread_id = intval($_GET['id']);

    // ページ番号を取得
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 5; // 1ページに表示するコメントの数
    $offset = ($page - 1) * $limit; // DBのどの行からデータを取得するか。$pageが1なら$offsetは0で、$pageが2なら$offsetは5で5行目からデータ取得。

    // threadsテーブルから指定されたスレッドIDに対応するスレッド情報を取得。そのスレッドを作成したユーザーの名前を取得。
    $stmt = $pdo->prepare("SELECT threads.*, CONCAT(members.name_sei, ' ', members.name_mei) AS member_name FROM threads JOIN members ON threads.member_id = members.id WHERE threads.id = :id");
    $stmt->bindParam(':id', $thread_id, PDO::PARAM_INT);
    $stmt->execute();
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        throw new Exception("スレッドが見つかりません。");
    }

    // commentsテーブルから総reaction数を取得
    $stmt = $pdo->prepare("SELECT COUNT(*) as reaction_count FROM comments WHERE thread_id = :thread_id");
    $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
    $stmt->execute();
    $reaction_count = $stmt->fetchColumn();

    // commentsテーブルからコメント情報を取得。指定されたIDに対応するコメント情報をDBから取得。そのコメントを投稿したユーザの名前を結合して取得。
    $stmt = $pdo->prepare("
    SELECT comments.*, 
           CONCAT(members.name_sei, ' ', members.name_mei) AS member_name,
           COUNT(likes.id) AS like_count,
           EXISTS(SELECT 1 FROM likes WHERE likes.comment_id = comments.id AND likes.member_id = :user_id) AS user_liked
    FROM comments 
    JOIN members ON comments.member_id = members.id 
    LEFT JOIN likes ON comments.id = likes.comment_id
    WHERE comments.thread_id = :thread_id 
    GROUP BY comments.id
    ORDER BY comments.created_at ASC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
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

    .tight-lines {
        line-height: 1;

    }

    .tight-lines li {
        float: left;
        list-style:none;
    }
    .like-button {
        cursor: pointer;
    }
    .heart {
        color: #000;
        transition: color 0.3s ease;
    }
    .heart.liked {
        color: red;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var isLoggedIn = <?php echo json_encode($loggedIn); ?>;
</script>

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

            <!-- ページネーションはコメントがある時表示、ない時表示させない？？グレーアウトだけ？消したら常に表示 -->
            <?php if (!empty($comments)): ?>
                <div class="pagination tight-lines">
                    <ul>
                    <li><a class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page - 1; ?>">前へ<br> &nbsp; &gt;</a></li>
                    <li><a class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page + 1; ?>">次へ<br> &nbsp; &gt;</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <br>
            <br>
            <!-- 投稿者氏名、投稿日時、コメントを表示 -->
            投稿者：<?php echo htmlspecialchars($thread['member_name']); ?>
            <?php echo htmlspecialchars($thread['created_at']); ?>
            <p><?php echo htmlspecialchars($thread['content']); ?></p>

            <!-- コメントループ-->
            <?php if (!empty($comments)): ?>

                <ul style="list-style-type: none;">
                    <?php foreach ($comments as $comment): ?>
                        <li>
                            <?php echo htmlspecialchars($comment['id']); ?>. <!-- コメントID -->
                            <?php echo htmlspecialchars($comment['member_name']); ?> <!-- コメ主氏名 -->
                            <?php echo htmlspecialchars($comment['created_at']); ?> <!-- 投稿日時 -->
                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p> <!-- コメント -->
                            <!-- いいね機能 -->
                            <div class="like-button" data-comment-id="<?php echo $comment['id']; ?>">
                                <span class="heart <?php echo ($loggedIn && $comment['user_liked']) ? 'liked' : ''; ?>">
                                    <?php echo ($loggedIn && $comment['user_liked']) ? '♥' : '♡'; ?>
                                </span>
                                <span class="like-count"><?php echo $comment['like_count']; ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>       
                </ul>

                <!-- ページネーション -->
                <div class="pagination tight-lines">
                    <ul>
                    <li><a class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page - 1; ?>">前へ<br> &nbsp; &gt;</a></li>
                    <li><a class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>" href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page + 1; ?>">次へ<br> &nbsp; &gt;</a></li>
                    </ul>
                </div>
                <br><br>
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

    <script>
    $(document).ready(function() {
        $('.like-button').click(function() {
            var commentId = $(this).data('comment-id');
            var likeButton = $(this);

            // いいねを押したけどログアウト状態だった時
            if (!isLoggedIn) {
                // 会員登録フォームへのリダイレクト
                window.location.href = 'member_regist.php'; 
                return;
            } 


            $.ajax({
                url: 'thread_detail.php',
                type: 'POST',
                data: {
                    like_comment_id: commentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var heartSpan = likeButton.find('.heart');
                        var countSpan = likeButton.find('.like-count');
                        
                        if (response.liked) {
                            heartSpan.addClass('liked').html('♥');
                        } else {
                            heartSpan.removeClass('liked').html('♡');
                        }
                        
                        countSpan.text(response.likeCount);
                    }
                }
            });
        });

    // ページ読み込み時といいねボタンクリック後にいいねの表示を更新
    function updateLikeDisplay() {
        $('.like-button').each(function() {
            var heartSpan = $(this).find('.heart');
            var likeCount = parseInt($(this).find('.like-count').text());
            
            if (!isLoggedIn || !heartSpan.hasClass('liked')) {
                heartSpan.html('♡').removeClass('liked');
            }
        });
    }

    // ページ読み込み時に実行
    updateLikeDisplay();

    // いいねボタンクリック後にも実行
    $('.like-button').on('click', function() {
        setTimeout(updateLikeDisplay, 100); // Ajax完了を待つため少し遅延させる
    });
});
    </script>
</body>
</html>