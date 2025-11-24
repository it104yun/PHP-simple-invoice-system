// js/main.js

/**
 * 彈出確認視窗，確認是否刪除指定的發票
 * @param {number} invoiceId 要刪除的發票 ID
 */
function confirmDelete(invoiceId) {
    if (confirm("您確定要刪除這筆發票記錄嗎？此操作不可逆！")) {
        // 如果使用者點擊確定，則導向 delete.php 執行刪除
        window.location.href = 'delete.php?id=' + invoiceId;
    }
}

/**
 * 彈出視窗提示使用者選擇發票格式
 * @param {number} invoiceId 要產生發票的 ID
 */
function promptGenerate(invoiceId) {
    const format = prompt(
        "請選擇要產生的發票格式：\n" +
        "1. 傳統三聯式發票\n" +
        "2. 電子計算機發票\n" +
        "3. 電子三聯式發票\n" + 
        "（請輸入 1, 2 或 3）"
    );

    if (format === '1') {
        // 傳統三聯式
        window.open(`generate.php?id=${invoiceId}&format=triplicate`, '_blank');
    } else if (format === '2') {
        // 電子計算機發票
        window.open(`generate.php?id=${invoiceId}&format=computer`, '_blank');
    } else if (format === '3') {
        // 電子三聯式發票 (使用一個新的參數名稱，例如 e_triplicate)
        window.open(`generate.php?id=${invoiceId}&format=e_triplicate`, '_blank');
    } else if (format !== null && format !== '') {
        alert("輸入無效，請輸入 1, 2 或 3。");
    }
}


// -------------------------------------------------------------
// [新增程式碼] 即時計算總金額
// -------------------------------------------------------------

/**
 * 根據未稅金額計算含稅總金額 (假設稅率為 5%)
 */
function calculateTotalAmount() {
    // 獲取未稅金額的 input 元素
    const amountInput = document.getElementById('amount');
    
    // 獲取顯示總金額的 span/div 元素
    const totalDisplay = document.getElementById('total_amount_display');

    if (!amountInput || !totalDisplay) {
        // 如果元素不存在 (例如在 index.php 頁面) 則不執行
        return;
    }

    // 取得輸入值，並嘗試轉換為浮點數
    let amount = parseFloat(amountInput.value);

    // 簡單驗證輸入是否有效
    if (isNaN(amount) || amount < 0) {
        totalDisplay.textContent = '0.00';
        return;
    }

    const TAX_RATE = 0.05; // 5% 稅率
    
    // 計算總金額 (含稅)
    let totalAmount = amount * (1 + TAX_RATE);
    
    // 格式化輸出，保留兩位小數
    totalDisplay.textContent = totalAmount.toFixed(2);
}

// 確保 DOM 載入後才執行初始化
document.addEventListener('DOMContentLoaded', function() {
    // 獲取未稅金額的 input 元素
    const amountInput = document.getElementById('amount');
    
    if (amountInput) {
        // 1. 頁面載入時先執行一次計算 (適用於編輯頁面)
        calculateTotalAmount();
        
        // 2. 監聽 input 欄位的輸入事件 (即時更新)
        amountInput.addEventListener('input', calculateTotalAmount);
    }
});