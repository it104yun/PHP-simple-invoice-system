<?php
// create.php

// 引入資料庫連線
require_once 'config/database.php';

$message = '';

// 檢查是否為 POST 請求 (表單提交)
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
            // 準備 SQL 插入語句 (使用 PDO 預處理語句防止 SQL 注入)
            $sql = "INSERT INTO invoices (invoice_number, invoice_date, customer_name, tax_id, amount) 
                    VALUES (:invoice_number, :invoice_date, :customer_name, :tax_id, :amount)";
            $stmt = $pdo->prepare($sql);
            
            // 綁定參數
            $stmt->bindParam(':invoice_number', $invoice_number);
            $stmt->bindParam(':invoice_date', $invoice_date);
            $stmt->bindParam(':customer_name', $customer_name);
            $stmt->bindParam(':tax_id', $tax_id);
            $stmt->bindParam(':amount', $amount);
            
            // 執行
            $stmt->execute();
            
            // 成功後導向列表頁
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            // 處理錯誤，例如發票號碼重複
            if ($e->getCode() == 23000) { // 23000 是 MySQL 唯一性約束違規的錯誤代碼
                 $message = '<p style="color: red;">新增失敗：發票號碼已存在！</p>';
            } else {
                $message = '<p style="color: red;">新增發票失敗: ' . $e->getMessage() . '</p>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>新增電子發票</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .form-actions { margin-top: 20px; }
        .form-actions button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 新增電子發票</h2>
        
        <?php echo $message; // 顯示錯誤或成功訊息 ?>

        <form method="POST">
            <div class="form-group">
                <label for="invoice_number">發票號碼 <span style="color: red;">*</span></label>
                <input type="text" id="invoice_number" name="invoice_number" required maxlength="10">
            </div>
            
            <div class="form-group">
                <label for="invoice_date">開立日期 <span style="color: red;">*</span></label>
                <input type="date" id="invoice_date" name="invoice_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="customer_name">客戶名稱 <span style="color: red;">*</span></label>
                <input type="text" id="customer_name" name="customer_name" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="tax_id">統一編號</label>
                <input type="text" id="tax_id" name="tax_id" maxlength="10">
            </div>
            
            <div class="form-group">
                <label for="amount">金額 (未稅) <span style="color: red;">*</span></label>
                <input type="number" step="0.01" id="amount" name="amount" required min="0.01">
            </div>
            <!-- 進階新增 -->
            <p>
                <strong>預估總金額 (含稅 5%):</strong> $<span id="total_amount_display">0.00</span>
            </p>

            <div class="form-actions">
                <button type="submit">儲存發票</button>
                <a href="index.php" style="margin-left: 10px;">返回列表</a>
            </div>
        </form>
    </div>
    <!-- 進階新增 -->
    <script src="js/main.js"></script>
</body>
</html>