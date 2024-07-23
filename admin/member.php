<?php
session_start();

// 管理者用member.phpはログインしてる管理者だけが見れるように
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

// 都道府県のリスト（検索の時に使う）
$prefectures = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
    '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
    '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
    '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
    '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
];

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

// 検索条件の取得
$searchId = isset($_GET['search_id']) ? $_GET['search_id'] : '';
$searchGender = isset($_GET['search_gender']) ? $_GET['search_gender'] : [];
$searchPref = isset($_GET['search_pref']) ? $_GET['search_pref'] : '';
$searchKeyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';

try {
    // DBへの接続を確立
    $pdo = new PDO($dsn, $username, $password);
    // エラー発生時に例外をスローするように設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 検索クエリの作成
    $query = "SELECT COUNT(*) FROM members WHERE deleted_at IS NULL";
    $params = [];
    if (!empty($searchId)) {
        $query .= " AND id = :id";
        $params[':id'] = $searchId;
    }
    if (!empty($searchGender)) {
        $genderPlaceholders = [];
        foreach ($searchGender as $index => $gender) {
            $genderPlaceholders[] = ':gender' . $index;
            $params[':gender' . $index] = $gender;
        }
        $query .= " AND gender IN (" . implode(',', $genderPlaceholders) . ")";
    }
    if (!empty($searchPref)) {
        $query .= " AND pref_name = :pref";
        $params[':pref'] = $searchPref;
    }
    if (!empty($searchKeyword)) {
        $query .= " AND (name_sei LIKE :keyword OR name_mei LIKE :keyword)";
        $params[':keyword'] = '%' . $searchKeyword . '%';
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $total_members = $stmt->fetchColumn();

    $total_pages = ceil($total_members / $limit);

    // DBのmemberテーブルから会員情報を取得（ソート順を反映、検索条件を考慮）
    $query = "SELECT id, CONCAT(name_sei, name_mei) as name, gender, CONCAT(pref_name, address) as address, created_at 
    FROM members 
    WHERE deleted_at IS NULL";
    
    $queryParams = [];
    // ID
    if (!empty($searchId)) {
        $query .= " AND id = :id";
        $queryParams[':id'] = $searchId;
    }
    // 性別
    if (!empty($searchGender)) {
        $genderPlaceholders = [];
        foreach ($searchGender as $index => $gender) {
            $genderPlaceholders[] = ':gender' . $index;
            $queryParams[':gender' . $index] = $gender;
        }
        $query .= " AND gender IN (" . implode(',', $genderPlaceholders) . ")";
    }
    // 都道府県
    if (!empty($searchPref)) {
        $query .= " AND pref_name = :pref";
        $queryParams[':pref'] = $searchPref;
    }
    // フリーワード
    if (!empty($searchKeyword)) {
        $query .= " AND (name_sei LIKE :keyword OR name_mei LIKE :keyword)";
        $queryParams[':keyword'] = '%' . $searchKeyword . '%';
    }

    $query .= " ORDER BY " . ($sort == 'created_at' ? 'created_at' : $sort) . " $order LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    foreach ($queryParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '接続失敗: ' . $e->getMessage();
}

// 変更箇所：ページネーションリンクの生成
$pagination_link = "?sort=$sort&order=$order";
if (!empty($searchId)) $pagination_link .= "&search_id=" . urlencode($searchId);
if (!empty($searchGender)) {
    foreach ($searchGender as $gender) {
        $pagination_link .= "&search_gender[]=" . urlencode($gender);
    }
}
if (!empty($searchPref)) $pagination_link .= "&search_pref=" . urlencode($searchPref);
if (!empty($searchKeyword)) $pagination_link .= "&search_keyword=" . urlencode($searchKeyword);
$pagination_link .= "&page=";

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
    <div>
    <form action="member.php" method="GET">
        <table border="1" width="70%">
            <tr>
                <th bgcolor="gray">ID</th>
                <td><input type="text" name="search_id" value="<?php echo htmlspecialchars($searchId); ?>"></td>
            </tr>
            <tr>
                <th bgcolor="gray">性別</th>
                <td>
                    <input type="checkbox" name="search_gender[]" value="1" <?php echo in_array('1', $searchGender) ? 'checked' : ''; ?>>男性
                    <input type="checkbox" name="search_gender[]" value="2" <?php echo in_array('2', $searchGender) ? 'checked' : ''; ?>>女性
                </td>
            </tr>
            <tr>
                <th bgcolor="gray">都道府県</th>
                <td>
                    <select name="search_pref">
                    <option value="">選択してください</option>
                    <?php foreach ($prefectures as $prefecture): ?>
                    <option value="<?php echo htmlspecialchars($prefecture, ENT_QUOTES); ?>" <?php echo $searchPref == $prefecture ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($prefecture, ENT_QUOTES); ?>
                    </option>
                    <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th bgcolor="gray">フリーワード</th>
                <td>
                    <input type="text" name="search_keyword" value="<?php echo htmlspecialchars($searchKeyword); ?>">
                </td>
            </tr>
        </table>
        <p><input type="submit" value="検索する"></p>
</form>
    </div>

    <!-- 会員一覧 1ページあたり10件 -->
    <!-- 会員がいれば -->
    <?php if (isset($members) && count($members) > 0): ?>
        <table border="1" width="70%">
            <tr bgcolor="gray">
                <th>
                    <!-- 変更箇所：ソートリンク -->
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => $reverse_order, 'page' => 1])); ?>" class="sort-link">
                        ID<?php echo ($sort == 'id') ? ($order == 'ASC' ? '▲' : '▼') : ''; ?>
                    </a>
                </th>
                <th>氏名</th>
                <th>性別</th>
                <th>住所</th>
                <th>
                    <!-- 変更箇所：ソートリンク -->
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'created_at', 'order' => $reverse_order, 'page' => 1])); ?>" class="sort-link">
                        登録日時<?php echo ($sort == 'created_at') ? ($order == 'ASC' ? '▲' : '▼') : '▼'; ?>
                    </a>
                </th>
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

            // 「前へ」リンク
            if ($page > 1): ?>
                <a href="<?php echo $pagination_link . ($page - 1); ?>">前へ&gt;</a>
            <?php endif; 

            // ページ番号リンク
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
