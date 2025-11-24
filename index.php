<?php
// index.php

// 引入資料庫連線
require_once 'config/database.php';

// 查詢所有發票資料: 使用分查詢與搜尋功能
// --- [ 1. 分頁設定 ] ---
// $records_per_page = 10; // 每頁顯示 10 筆記錄
$records_per_page = 5; // 每頁顯示 5 筆記錄
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// --- [ 2. 搜尋設定 ] ---
$search_term = '';
$search_condition = '';
$search_params = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    // 使用萬用字元 % 進行模糊搜尋 (搜尋發票號碼或客戶名稱)
    $search_condition = ' WHERE invoice_number LIKE :search OR customer_name LIKE :search ';
    $search_params[':search'] = '%' . $search_term . '%';
}

// --- [ 3. 排序設定 ] ---
$allowed_sorts = ['invoice_number', 'customer_name', 'invoice_date', 'amount', 'total_amount'];  // 這裡包含所有允許排序的欄位名稱 (對應資料庫欄位)
$default_sort = 'invoice_date'; // 預設按日期排序
$default_order = 'DESC'; // 預設降序 (最新在最前)

// 獲取當前排序欄位，確保是允許的欄位
$sort_by = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts) ? $_GET['sort'] : $default_sort;

// 獲取當前排序方向，確保是 ASC 或 DESC
$sort_order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : $default_order;

// 根據排序欄位和方向建構 ORDER BY 子句
$order_by_clause = " ORDER BY " . $sort_by . " " . $sort_order;

// --- [ 4. 獲取總記錄數 (用於分頁) ] ---
try {
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM invoices" . $search_condition);
    $stmt_count->execute($search_params);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    die("查詢總記錄數失敗: " . $e->getMessage());
}

// --- [ 5. 獲取當前頁面發票數據 ] ---
try {
    // 結合搜尋條件和排序子句
    $sql = "SELECT * FROM invoices" . $search_condition . $order_by_clause . " LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    
    // 綁定搜尋參數
    foreach ($search_params as $key => &$value) {
        $stmt->bindParam($key, $value);
    }
    
    // 綁定分頁參數
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    
    $stmt->execute();
    $invoices = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("查詢發票資料失敗: " . $e->getMessage());
}


// 輔助函式：建立排序連結
function buildSortLink($field, $label, $current_sort, $current_order, $search_term) {
    // 1. 決定新的排序方向
    if ($field == $current_sort) {
        $new_order = ($current_order == 'ASC') ? 'DESC' : 'ASC';
    } else {
        $new_order = 'DESC'; // 新欄位預設降序
    }
    
    // 2. 建立查詢參數
    $query = "?sort=" . $field . "&order=" . $new_order;
    if (!empty($search_term)) {
        $query .= "&search=" . urlencode($search_term);
    }
    
    // 3. 顯示排序箭頭
    $arrow = '';
    if ($field == $current_sort) {
        $arrow = ($current_order == 'ASC') ? '▲' : '▼';
    }
    
    return "<a href=\"index.php" . $query . "\" style=\"text-decoration: none; color: inherit;\">" 
           . $label . " " . $arrow . "</a>";
}

// 定義哪些欄位可以排序 (Key: 資料庫欄位名, Value: 顯示名稱)
$sortable_fields = [
    'invoice_number' => '發票號碼',
    'invoice_date' => '開立日期',
    'customer_name' => '客戶名稱',
    'tax_id' => '統一編號 (不可排序)', // 統一編號不參與排序，但仍需顯示
    'amount' => '金額 (未稅)',
    'total_amount' => '總金額 (含稅)',
];


// 關閉資料庫連線
$pdo = null;
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>簡易電子發票系統 - 列表</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/main.js"></script>
</head>
<body>
    <div class="container">
        <h2>🧾 電子發票列表</h2>

        <!-- 搜尋表單，讓使用者可以輸入發票號碼或客戶名稱來搜尋發票記錄。 -->
        <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="輸入發票號碼或客戶名稱..." 
                   value="<?php echo htmlspecialchars($search_term); ?>" 
                   style="padding: 8px; border: 1px solid #ccc; flex-grow: 1;">
            <button type="submit" style="padding: 8px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px;">🔍 搜尋</button>
            <?php if (!empty($search_term)): ?>
                <a href="index.php" style="padding: 8px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">清除搜尋</a>
            <?php endif; ?>
        </form>

        <a href="create.php" class="btn">➕ 新增發票</a>

        <?php if (count($invoices) > 0): ?>
            <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr>
                        <?php 
                        foreach ($sortable_fields as $field => $label): 
                            // 檢查該欄位是否在允許排序的清單中
                            $is_sortable = in_array($field, ['invoice_number', 'invoice_date', 'customer_name', 'amount', 'total_amount']);
                        ?>
                            <th style="<?php echo $is_sortable ? 'cursor: pointer;' : ''; ?>">
                                <?php if ($is_sortable): ?>
                                    <?php echo buildSortLink($field, $label, $sort_by, $sort_order, $search_term); ?>
                                <?php else: ?>
                                    <?php echo $label; ?>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                        
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['invoice_date']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['tax_id'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td>$<?php echo number_format($invoice['total_amount'], 2); ?></td>
                            <td class="action-links">
                                <a  href="edit.php?id=<?php echo $invoice['id']; ?>">編輯</a> |
                                <button type="button" class="btn" onclick="confirmDelete(<?php echo $invoice['id']; ?>)">刪除</button> |
                                <button type="button" class="btn" style="color:black;" onclick="promptGenerate(<?php echo $invoice['id']; ?>)">產生發票</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>目前沒有任何發票記錄。</p>
        <?php endif; ?>
    
        <div class="pagination" style="margin-top: 20px; text-align: center;">
            <?php if ($total_pages > 1): ?>
                <?php
                    // 輔助函式：建立基本 URL 參數字串 (用於保留搜尋詞)
                    $base_query = '';
                    if (!empty($search_term)) {
                        $base_query .= "&search=" . urlencode($search_term);
                    }
                    // 保留排序參數
                    $base_query .= "&sort=" . urlencode($sort_by) . "&order=" . urlencode($sort_order);
                ?>

                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?><?php echo $base_query; ?>" style="margin-right: 10px;">&laquo; 上一頁</a>
                <?php endif; ?>

                <?php 
                // 簡易分頁連結：只顯示當前頁附近幾頁
                    $start = max(1, $current_page - 2);
                    $end = min($total_pages, $current_page + 2);

                    for ($i = $start; $i <= $end; $i++): 
                        $active_style = ($i == $current_page) ? 'background-color: #007bff; color: white; border: 1px solid #007bff; padding: 5px 10px; border-radius: 3px; text-decoration: none;' : 'padding: 5px 10px; border: 1px solid #ccc; border-radius: 3px; text-decoration: none;';
                        ?>
                        <a href="?page=<?php echo $i; ?><?php echo $base_query; ?>" style="<?php echo $active_style; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?><?php echo $base_query; ?>" style="margin-left: 10px;">下一頁 &raquo;</a>
                    <?php endif; ?>
                <p style="margin-top: 10px; font-size: 0.9em; color: #666;">總共 <?php echo $total_records; ?> 筆記錄，共 <?php echo $total_pages; ?> 頁</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>