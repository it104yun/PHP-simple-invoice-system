<?php
// generate.php

// 引入資料庫連線
require_once 'config/database.php';

// 檢查參數
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['format']) || empty($_GET['format'])) {
    die("錯誤: 缺少發票 ID 或格式參數。");
}

$id = (int) $_GET['id'];
$format = $_GET['format'];

// 讀取單筆發票資料
try {
    $stmt = $pdo->prepare('SELECT * FROM invoices WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        die("錯誤: 找不到該發票記錄。");
    }
} catch (PDOException $e) {
    die("讀取發票資料失敗: " . $e->getMessage());
}

// 根據格式設定標題和版面樣式
$title = '';
$css_file = '';

if ($format === 'triplicate') {
    $title = '傳統三聯式發票';
    $css_file = 'triplicate.css';
} elseif ($format === 'computer') {
    $title = '電子計算機發票';
    $css_file = 'computer.css';
} elseif ($format === 'e_triplicate') { // <-- 新增邏輯判斷
    $title = '電子三聯式發票';
    $css_file = 'e_triplicate.css';
} else {
    die("錯誤: 無效的發票格式。");
}

// 輔助函式：將金額轉為中文大寫
function amountToChinese($number) {
    $cnyUnits = ["", "拾", "佰", "仟"];
    $cnyDigits = ["零", "壹", "貳", "參", "肆", "伍", "陸", "柒", "捌", "玖"];
    
    // (此處省略複雜的中文大寫轉換邏輯，實際應用中應使用完整函式)
    // 為了簡潔，這裡只回傳數字加上註解
    return number_format($number, 2) . " (此處應為中文大寫)";
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>產生 <?php echo $title; ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/<?php echo htmlspecialchars($css_file); ?>">
    <style>
        /* 通用列印樣式 */
        .invoice-paper {
            border: 2px solid black;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
            font-size: 14px;
        }
        .header h1 { text-align: center; margin-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .section-box { border: 1px solid #333; padding: 10px; margin-top: 15px; }
        .table-invoice th, .table-invoice td { padding: 8px; border: 1px solid #333; }
        .print-btn { text-align: center; margin-top: 20px; }
        @media print {
            .print-btn { display: none; }
            .invoice-paper { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-paper">
        <?php if ($format === 'triplicate'): ?>
            <div class="header">
                <h1>統一發票 (三聯式)</h1>
            </div>
            <div class="info-row">
                <span>買受人名稱：<?php echo htmlspecialchars($invoice['customer_name']); ?></span>
                <span>發票號碼：<?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
            </div>
            <div class="info-row">
                <span>統一編號：<?php echo htmlspecialchars($invoice['tax_id'] ?? '______'); ?></span>
                <span>開立日期：<?php echo htmlspecialchars($invoice['invoice_date']); ?></span>
            </div>
            
            <table class="table-invoice" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 50%;">品名</th>
                        <th style="width: 20%;">數量</th>
                        <th style="width: 30%;">單價/金額</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>商品銷售一批</td>
                        <td>1</td>
                        <td style="text-align: right;"><?php echo number_format($invoice['amount'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="section-box" style="margin-top: 10px;">
                <p><strong>銷售額 (未稅):</strong> $<?php echo number_format($invoice['amount'], 2); ?></p>
                <p><strong>營業稅額 (5%):</strong> $<?php echo number_format($invoice['total_amount'] - $invoice['amount'], 2); ?></p>
                <p style="font-size: 1.2em; font-weight: bold;"><strong>總計 (含稅):</strong> $<?php echo number_format($invoice['total_amount'], 2); ?></p>
                <p><strong>總計 (中文大寫):</strong> <?php echo amountToChinese($invoice['total_amount']); ?></p>
            </div>
            
        <?php elseif ($format === 'computer'): ?>
            <div class="header">
                <h1>電子計算機統一發票</h1>
            </div>
            <div class="info-row">
                <span style="font-size: 1.5em; font-weight: bold;">發票字軌號碼：<?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                <span>日期：<?php echo htmlspecialchars($invoice['invoice_date']); ?></span>
            </div>
            
            <table class="table-invoice" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tr>
                    <td style="width: 30%;">買受人名稱</td>
                    <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                    <td style="width: 30%;">統一編號</td>
                    <td><?php echo htmlspecialchars($invoice['tax_id'] ?? '無'); ?></td>
                </tr>
            </table>

            <div class="section-box">
                <p>銷售合計：$<?php echo number_format($invoice['amount'], 2); ?></p>
                <p>稅額 (5%)：$<?php echo number_format($invoice['total_amount'] - $invoice['amount'], 2); ?></p>
                <p style="font-size: 1.4em; color: darkred;"><strong>合計金額：$<?php echo number_format($invoice['total_amount'], 2); ?></strong></p>
            </div>
            <p style="text-align: center; margin-top: 30px;">請妥善保管本發票以利對獎。</p>
        <?php elseif ($format === 'e_triplicate'): ?>
            <div class="header">
                <h1 style="color: blue;">電子三聯式發票證明聯 (銷貨)</h1>
                <p style="text-align: center; margin-top: -10px;">本欄為憑證，請勿折損</p>
            </div>
            
            <div class="info-row" style="border: 1px dashed #333; padding: 5px;">
                <span style="font-weight: bold;">發票號碼：<?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                <span>日期：<?php echo htmlspecialchars($invoice['invoice_date']); ?></span>
            </div>
            <div class="info-row">
                <span>買受人名稱：<?php echo htmlspecialchars($invoice['customer_name']); ?></span>
                <span>統一編號：<?php echo htmlspecialchars($invoice['tax_id'] ?? '______'); ?></span>
            </div>
            
            <table class="table-invoice" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr>
                        <th>品名摘要</th>
                        <th style="width: 20%;">數量</th>
                        <th style="width: 30%;">單價/金額</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>電子發票銷售服務</td>
                        <td>1</td>
                        <td style="text-align: right;"><?php echo number_format($invoice['amount'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="section-box" style="margin-top: 10px; border-color: blue;">
                <p><strong>銷售額 (未稅):</strong> $<?php echo number_format($invoice['amount'], 2); ?></p>
                <p><strong>營業稅額 (5%):</strong> $<?php echo number_format($invoice['total_amount'] - $invoice['amount'], 2); ?></p>
                <p style="font-size: 1.3em; font-weight: bold; color: darkred;"><strong>總計 (含稅):</strong> $<?php echo number_format($invoice['total_amount'], 2); ?></p>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 20px; align-items: center;">
                <div style="width: 150px; height: 150px; border: 1px solid #000; text-align: center; line-height: 150px; font-size: 10px;">
                    [模擬 QR Code 圖]
                </div>
                <div style="text-align: right;">
                    <p>買方隨機碼：A1B2C3D4</p>
                    <p>開立機關：XX 國稅局</p>
                    <p>賣方統編：87654321</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="print-btn">
        <button onclick="window.print()">列印發票</button>
        <button onclick="window.close()">關閉視窗</button>
    </div>
    
</body>
</html>