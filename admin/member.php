<?php
session_start();

// 管理者用member.phpはログインしてる管理者だけが見れるように?
// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// セッションから値を取得
$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? $_SESSION['user_name'] : '';

// ログアウト処理
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy(); // セッションを破棄
    header('Location: login.php'); // ログインページにリダイレクト
    exit();
}

$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

// ページネーションのためページ番号を取得
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // 1ページに表示するコメントの数
$offset = ($page - 1) * $limit; // DBのどの行からデータを取得するか。$pageが1なら$offsetは0で、$pageが2なら$offsetは10で10行目からデータ取得。

// ソート順の取得
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// 逆のソート順を決定
$reverse_order = ($order == 'ASC') ? 'DESC' : 'ASC';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        // DBへの接続を確立
        $pdo = new PDO($dsn, $username, $password);
        // エラー発生時に例外をスローするように設定
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ページネーションのためmembersテーブルから総member数を取得。さらに、membersテーブルのdeleted_atカラムがNULLである場合のみデータを取得
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE deleted_at IS NULL');
        $stmt->execute();
        $total_members = $stmt->fetchColumn();

        $total_pages = ceil($total_members / $limit);

        // DBのmemberテーブルから会員情報を取得（ソート順を反映）
        $stmt = $pdo->prepare("SELECT id, CONCAT(name_sei, name_mei) as name, gender, CONCAT(pref_name, address) as address, created_at 
        FROM members 
        WHERE deleted_at IS NULL
        ORDER BY $sort $order
        LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);



    } catch (PDOException $e) {
        echo '接続失敗: ' . $e->getMessage();
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>会員一覧</title>
    <style>
        li {
            list-style-type: none;
            display: inline;
        }
        .pagination {
            margin: 20px 0;
        }
        .pagination a {
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
        }
        .pagination a.current {
            background-color: #808080;
            color: white;
            border-color: #808080;
        }
        .sort-link {
            text-decoration: none;
            color: black;
        }
    </style>
</head>
<body>
    <header>
        <ul>
            <li><strong>会員一覧</strong></li>
            <li><a href="admin_top.php">トップへ戻る</a></li>
        </ul>
    </header>

    <main>
    <!-- 会員検索 -->

    <!-- 会員一覧 1ページあたり10件 -->
    <!-- 会員がいれば -->
    <?php if (isset($members) && count($members) > 0): ?>
        <table border="1" width="70%">
            <tr bgcolor="gray">
                <th>
                    <a href="?page=<?php echo $page; ?>&sort=id&order=<?php echo $reverse_order; ?>" class="sort-link">
                        ID<?php echo ($sort == 'id') ? ($order == 'ASC' ? '▲' : '▼') : ''; ?>
                    </a>
                </th>
                <th>氏名</th>
                <th>性別</th>
                <th>住所</th>
                <th>登録日時</th>
            </tr>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?php echo htmlspecialchars($member['id']); ?></td>
                <td><?php echo htmlspecialchars($member['name']); ?></td>
                <td>
                    <?php 
                    if ($member['gender'] == 1) {
                        echo '男性';
                    } elseif ($member['gender'] == 2) {
                        echo '女性';
                    } else {
                        echo 'その他';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($member['address']); ?></td>
                <td><?php echo htmlspecialchars($member['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- ページネーション -->
        <div class="pagination">
            <?php
            // 表示するページ番号の範囲を決定
            $range = 1; // 現在のページの前後に表示するページ数
            $start = max(1, min($page - $range, $total_pages - 2));
            $end = min($total_pages, max($page + $range, 3));
            $pagination_link = "?sort=$sort&order=$order&page=";

            // 「前へ」リンク
            if ($page > 1): ?>
                <a href="<?php echo $pagination_link . ($page - 1); ?>">前へ&gt;</a>
            <?php endif; 

            // ページ番号リンク 今いるページ番号のクラス名をcurrentにしてCSSで強調
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo $pagination_link . $i; ?>" <?php echo ($i == $page) ? 'class="current"' : ''; ?>><?php echo $i; ?></a>
            <?php endfor;

            // 「次へ」リンク
            if ($page < $total_pages): ?>
                <a href="<?php echo $pagination_link . ($page + 1); ?>">次へ&gt;</a>
            <?php endif; ?>
        </div>

    <!-- 会員がいなければ -->
    <?php else: ?>
        <p>会員はいません。</p>
    <?php endif; ?>


    </main>
</body>
</html>