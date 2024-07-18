<?php
session_start();

// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "不正なリクエストです。";
        exit();
    }

    unset($_SESSION['csrf_token']);

    $formData = $_SESSION['formData'] ?? [];
    if (empty($formData)) {
        echo "フォームデータが存在しません。";
        exit;
    }

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $id = $_SESSION['user_id'];
        $title = $formData['title'];
        $comment = $formData['comment'];

        $stmt = $pdo->prepare("INSERT INTO threads (member_id, title, content) VALUES (:member_id, :title, :content)");
        $stmt->bindParam(':member_id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $comment);

        if ($stmt->execute()) {
            header('Location: thread.php');
            exit();
        } else {
            echo "スレッド作成中にエラーが発生しました。";
        }
    } catch (PDOException $e) {
        echo '接続失敗: ' . $e->getMessage();
    }
} else {
    // GETリクエストの処理（スレッド一覧表示）
    // GETリクエストがない場合に$searchが未定義になる可能性有。それを防ぐために初期化。
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $search = isset($_GET['search']) ? $_GET['search'] : '';

        if ($search) {
            $stmt = $pdo->prepare("SELECT * FROM threads WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC");
            $stmt->execute(['search' => "%{$search}%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM threads ORDER BY created_at DESC");
        }

        $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo '接続失敗: ' . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>スレッド一覧</title>
</head>
<body>
    <header>
        <a href="thread_regist.php">新規スレッド作成</a>
    </header>
    <main>

        <!-- スレッド検索 -->
        <div>
            <form action="thread.php" method="GET">
                <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="submit" value="検索">
            </form>
        </div>

        <!-- スレッド一覧 -->
        <div>
            <?php if (isset($threads)): ?>
                <ul  style="list-style-type: none;">
                <?php foreach ($threads as $thread): ?>
                    <li>
                        ID: <?php echo htmlspecialchars($thread['member_id']); ?><!-- 会員ID -->
                        <a href="thread_detail.php?id=<?php echo htmlspecialchars($thread['id']); ?>">
                            <?php echo htmlspecialchars($thread['title']); ?><!-- スレタイをリンクに -->
                        </a>
                        <?php echo htmlspecialchars($thread['created_at']); ?><!-- 作成日時 -->
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>スレッドがありません。</p>
            <?php endif; ?>
        </div>
        <form action="top.php" method="get">
          <input type="submit" value="トップに戻る">
        </form>
    </main>
</body>
</html>





