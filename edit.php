<?php
// edit.php

// 引入資料庫連線
require_once 'config/database.php';

$message = '';
$invoice = null;

// 檢查是否有傳入 ID 參數
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("錯誤: 缺少發票 ID。");
}

$id = (int) $_GET['id'];

// --- 處理 POST 請求 (更新資料) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 獲取並清理輸入資料
    $invoice_number = trim($_POST['invoice_number']);
    $invoice_date   = trim($_POST['invoice_date']);
    $customer_name  = trim($_POST['customer_name']);
    $tax_id         = trim($_POST['tax_id']);
    $amount         = (float) $_POST['amount'];

    // 簡單的資料驗證
    if (empty($invoice_number) || empty($invoice_date) || empty($customer_name) || empty($amount)) {
        $message = '<p style="color: red;">請填寫所有必填欄位！</p>';
    } else {
        try {
            // 準備 SQL 更新語句
            $sql = "UPDATE invoices SET 
                    invoice_number = :invoice_number, 
                    invoice_date = :invoice_date, 
                    customer_name = :customer_name, 
                    tax_id = :tax_id, 
                    amount = :amount
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            // 綁定參數
            $stmt->bindParam(':invoice_number', $invoice_number);
            $stmt->bindParam(':invoice_date', $invoice_date);
            $stmt->bindParam(':customer_name', $customer_name);
            $stmt->bindParam(':tax_id', $tax_id);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            // 執行
            $stmt->execute();
            
            $message = '<p style="color: green;">發票更新成功！</p>';
            // 成功後，需要重新讀取資料以顯示最新的值
            // (繼續執行下面的 SELECT 查詢)
            
        } catch (PDOException $e) {
            // 處理錯誤
            if ($e->getCode() == 23000) {
                 $message = '<p style="color: red;">更新失敗：發票號碼已存在！</p>';
            } else {
                $message = '<p style="color: red;">更新發票失敗: ' . $e->getMessage() . '</p>';
            }
        }
    }
}

// --- 處理 GET 請求 (讀取單筆資料) ---
try {
    $stmt = $pdo->prepare('SELECT * FROM invoices WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $invoice = $stmt->fetch();
    
    // 如果找不到發票
    if (!$invoice) {
        die("錯誤: 找不到該發票記錄。");
    }
} catch (PDOException $e) {
    die("讀取發票資料失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>編輯電子發票</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* 樣式與 create.php 相同，可以考慮抽離 */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .form-actions { margin-top: 20px; }
        .form-actions button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✍️ 編輯電子發票 #<?php echo htmlspecialchars($invoice['id']); ?></h2>
        
        <?php echo $message; // 顯示錯誤或成功訊息 ?>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($invoice['id']); ?>">
            
            <div class="form-group">
                <label for="invoice_number">發票號碼 <span style="color: red;">*</span></label>
                <input type="text" id="invoice_number" name="invoice_number" required maxlength="10" 
                       value="<?php echo htmlspecialchars($invoice['invoice_number']); ?>">
            </div>
            
            <div class="form-group">
                <label for="invoice_date">開立日期 <span style="color: red;">*</span></label>
                <input type="date" id="invoice_date" name="invoice_date" required 
                       value="<?php echo htmlspecialchars($invoice['invoice_date']); ?>">
            </div>
            
            <div class="form-group">
                <label for="customer_name">客戶名稱 <span style="color: red;">*</span></label>
                <input type="text" id="customer_name" name="customer_name" required maxlength="100"
                       value="<?php echo htmlspecialchars($invoice['customer_name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="tax_id">統一編號</label>
                <input type="text" id="tax_id" name="tax_id" maxlength="10"
                       value="<?php echo htmlspecialchars($invoice['tax_id'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="amount">金額 (未稅) <span style="color: red;">*</span></label>
                <input type="number" step="0.01" id="amount" name="amount" required min="0.01"
                       value="<?php echo htmlspecialchars($invoice['amount']); ?>">
            </div>
            
            <!-- <p><strong>總金額 (含稅):</strong> $<?php echo number_format($invoice['total_amount'], 2); ?></p> -->
             <!-- 替換為使用 JavaScript 標籤的即時顯示： -->
            <p>
                <strong>預估總金額 (含稅 5%):</strong> $<span id="total_amount_display"><?php echo number_format($invoice['total_amount'], 2); ?></span>
                <small>(即時計算)</small>
            </p>


            <div class="form-actions">
                <button type="submit">儲存修改</button>
                <a href="index.php" style="margin-left: 10px;">返回列表</a>
            </div>
        </form>
    </div>
</body>
</html>