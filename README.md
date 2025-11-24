---
---
# PHP 簡易電子發票開發流程說明
http://localhost/simple-invoice-system/

#### 使用工具：PHP+純Javascript+純HTML+純CSS+MySQL  
#### 說明：電子發票系統（E-Invoice System）涉及到的 CRUD（Create, Read, Update, Delete）操作和資料庫互動。
---
## 🎯 簡易電子發票系統：系統概覽
這個簡易系統將包含以下功能：CRUD 核心功能（Create, Read, Update, Delete）及後續添加的 **\"進階功能\"** 
<OL>
<b>---基礎功能---</b>
<LI>發票列表頁 (Read): 顯示所有發票記錄。 </li>
<LI>新增發票頁 (Create): 允許使用者輸入發票資訊並存入資料庫。 </li>
<LI>發票詳情/編輯頁 (Read/Update): 顯示單張發票的詳細資訊，並提供編輯功能。 </li>
<LI>刪除功能 (Delete): 刪除特定的發票記錄。 </li>
🚨<b>---進階功能---</b>🚨
<LI>即時計算總金額(加了稅款)</li>
<LI>產生發票: 產生實際的發票資料(發票產生器)</li>
   <ul>
     <li>傳統三聯式發票</li>
     <li>電子計算機發票</li>
     <li>電子三聯式發票</li>
   </ul>
<LI>搜尋功能</li>
<LI>分頁</li>
<LI>欄位(ASC/DESC)排序</li>
</OL>

---
---
# -----操作畫面DEMO-----
### 1.首頁-顯示所有發票
  ![-01-](/images/e-invoice-01-首頁.jpg "顯示所有發票")
---
### 2.新增發票
  ![-02-](/images/e-invoice-02-輸入發票.jpg "輸入發票")
---
### 3.編輯發票內容
  ![-03-](/images/e-invoice-03-編修發票內容.jpg "編修發票內容")
---
### 4.編輯發票內容
  ![-04-](/images/e-invoice-04-刪除發票.jpg "刪除發票")
---
### 5. 產生發票(列印,電子檔給客戶)
  ![-05-](/images/e-invoice-05-產生發票(列印,電子檔給客戶).jpg "產生發票(列印,電子檔給客戶)")
---
### 6.-->1-傳統三聯式發票
  ![-06-](/images/e-invoice-06-產生發票-1傳統三聯式發票.jpg "1-傳統三聯式發票")
---
### 7.-->2-電子計算機發票
  ![-07-](/images/e-invoice-07-產生發票-2電子計算機發票.jpg "2-電子計算機發票")
---
### 8.-->電子三聯式發票
  ![-08-](/images/e-invoice-08-產生發票-3電子三聯式發票.jpg "3-電子三聯式發票")


---
---
## 前置作業: 安裝網站開發套件，在 Windows 11 上建立最適合您的開發環境。 

XAMPP、WAMP 和 MAMP 都是將網站開發所需的幾個核心組件打包在一起的 一站式（All-in-one） 解決方案。它們的目的是讓開發者可以快速、簡單地在本機電腦上運行 PHP 和 MySQL 應用程式，而無需手動安裝和配置每個組件。 

### 建議安裝XAMPP，XAMPP是什麼? / 為什麼要選擇它? 

1. Apache, MariaDB, PHP, Perl跨平台  
2. 普及率最高，配置簡單，內建 FileZilla (FTP) 和 Mercury (Mail) 伺服器，功能完整。 

### 詳細說明各組件：

1. A \- Apache (網頁伺服器): 這是將您的 PHP/HTML 檔案傳送給瀏覽器的軟體。 
2. M \- MySQL / MariaDB (資料庫): 這是儲存您的發票資料的地方。MariaDB 是 MySQL 的一個分支，功能和指令幾乎完全相容。 
3. P \- PHP (程式語言): 這是執行您後端邏輯的語言。 
4. P \- Perl (額外語言): XAMPP 額外包含的腳本語言。 PhPMyAdmin: 所有套件都會包含這個網頁工具，用於方便地管理您的 MySQL 資料庫。 

---

# 🚀開始吧Starting \!

### 階段一：環境設定與資料庫設計

### 1. 資料庫設定 (MySQL)

首先，您需要在您的 MySQL 伺服器上建立一個資料庫和一個發票表格。

建立資料庫
```
 -- 建立資料庫
CREATE DATABASE IF NOT EXISTS simple_invoice_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

-- 使用此資料庫
USE simple_invoice_db; 

-- 建立發票表格 (Invoices Table)
CREATE TABLE IF NOT EXISTS invoices ( 
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    invoice_number VARCHAR(20) NOT NULL UNIQUE COMMENT '發票號碼', 
    invoice_date DATE NOT NULL COMMENT '開立日期', 
    customer_name VARCHAR(100) NOT NULL COMMENT '客戶名稱', 
    tax_id VARCHAR(10) NULL COMMENT '客戶統一編號', 
    amount DECIMAL(10, 2) NOT NULL COMMENT '金額 (未稅)', 
    tax_rate DECIMAL(5, 2) DEFAULT 0.05 COMMENT '稅率 (預設 5%)', 
    total_amount DECIMAL(10, 2) GENERATED ALWAYS AS (amount * (1 + tax_rate)) STORED COMMENT '總金額 (含稅)', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 

-- 插入一些範例資料
INSERT INTO invoices (invoice_number, invoice_date, customer_name, tax_id, amount) VALUES 
('AB12345678', '2025-11-20', 'Google Co.', '12345678', 1000.00), 
('CD98765432', '2025-11-21', 'Facebook Corp.', '87654321', 5500.50); 

```

### 2. 檔案結構

在您的專案根目錄下建立以下檔案和資料夾：

#### <u>啟始結構(專案開始時)</u>
```
simple-invoice-system/ 
├── index.php             (發票列表頁)
├── create.php            (新增發票頁) 
├── edit.php              (編輯發票頁) 
├── delete.php            (處理刪除請求) 
├── config/ 
│     └── database.php      (資料庫連線設定) 
├── css/ 
│     └── style.css         (樣式檔案) 
└── js/ 
       └── main.js           (JavaScript 檔案) 
```
#### <u>最終結構(專案完成時)</u>
```
  simple-invoice-system/
  ├── .gitignore            <-- 【Git 設定】必須忽略 .env 檔案
  ├── index.php             <-- 【列表主頁】顯示、分頁、搜尋、排序的核心頁面
  ├── create.php            <-- 【新增頁】處理新增發票 (C)
  ├── edit.php              <-- 【編輯頁】處理更新發票 (U)
  ├── delete.php            <-- 【刪除處理】後端刪除記錄 (D)
  ├── generate.php          <-- 【發票產生器】輸出三種格式的發票排版
  ├── README.md             <-- 【專案說明】GitHub 說明文件

  ├── config/               <-- 系統配置與連線設定
  │   ├── database.php      <-- 負責從環境變數讀取並建立 PDO 資料庫連線
  │   └── dotenv_loader.php <-- .env 檔案的解析與載入器
  │
  ├── includes/             <-- 頁面共用 HTML 區塊 (Header/Footer 模組)
  │   ├── header.php        <-- 頁面頂部 (<html>, <head>, <body> 開始)
  │   └── footer.php        <-- 頁面底部 (</body>, </html> 結束)
  │
  ├── css/                  <-- 樣式檔案
  │   ├── style.css         <-- 網站通用樣式 (表格、按鈕美化)
  │   ├── triplicate.css    <-- 傳統三聯式發票樣式
  │   ├── computer.css      <-- 電子計算機發票樣式
  │   └── e_triplicate.css  <-- 電子三聯式發票樣式
  │
  └── js/                   <-- 前端 JavaScript 邏輯
      └── main.js           <-- 包含即時計算、刪除確認、產生格式選擇等函式
```



### 3. 資料庫連線檔案 (config/database.php)*
這是所有 PHP 程式碼連接資料庫的關鍵檔案。

```
<?php
// config/database.php

// 資料庫設定
define('DB_HOST', 'localhost'); // 您的資料庫主機名稱 
define('DB_NAME', 'simple_invoice_db'); // 您的資料庫名稱 
define('DB_USER', 'root'); // 您的資料庫使用者名稱 
define('DB_PASS', ''); // 您的資料庫密碼

// 建立資料庫連線
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS, 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        ]
    );
} catch (PDOException $e) {
    // 連線失敗時，顯示錯誤訊息並停止執行
    die("資料庫連線失敗: " . $e->getMessage()); 
}
```

### 階段二：發票列表頁 (Read)  index.php
這是系統的首頁，顯示所有發票。

1. 樣式檔案 (css/style.css): 加入基本樣式讓頁面看起來更整潔。  
2.  列表頁 (index.php): 連接資料庫並取出所有發票資料。

### 階段三：新增發票頁 (Create)  create.php
讓使用者可以輸入新的發票資料。  
新增頁面 (create.php) : 處理表單的提交，並將資料存入資料庫。

### 階段四：編輯發票頁 (Update)   edit.php
讓使用者可以修改現有的發票資料。  
編輯頁面 (edit.php): 處理資料的讀取和更新。

### 階段五：刪除功能 (Delete) 與 JavaScript 互動   delete.php
我們將使用純 JavaScript 來實現刪除前的確認提示，並使用一個專門的 PHP 檔案處理刪除的後端邏輯。  
1\. 刪除邏輯 (delete.php): 這個檔案只負責接收 ID 並執行刪除操作，然後跳回列表頁。  
2\. JavaScript 確認 (js/main.js): 在發票列表頁 (index.php) 中使用的確認刪除功能。

**現在，您可以在您的本機環境XAMPP上運行這些檔案，並開始測試您的發票系統了!**
---
# 🚀 專案運行步驟

接下來，我們來進行專案的部署和測試。  
**1\. 確保您的 MySQL 服務正在運行**

* **檢查：** 請確認您單獨安裝的 MySQL 伺服器（非 XAMPP 內建的）已經啟動。通常在 Windows 服務列表中可以確認。  
* **登入資訊：** 確保您知道您的 MySQL **使用者名稱**（通常是 root）和**密碼**，這將用於連線。

**2\. 部署專案檔案到 XAMPP 目錄**  
XAMPP 的預設網頁根目錄是 htdocs。

* **找到目錄：**  
  * 假設您的 XAMPP 安裝在 C:\\xampp。  
  * 網頁根目錄就是：C:\\xampp\\htdocs\\  
* **放置專案：**  
  * 將您在前面步驟中建立的整個專案資料夾 (simple-invoice-system) 複製到 htdocs 下。  
  * 您的路徑結構應如下：  
  * C:\\xampp\\htdocs\\simple-invoice-system\\

### **3\. 配置資料庫連線 (config/database.php)**

您需要更新連線設定，確保 PHP 能夠透過您的使用者名稱和密碼連接到您獨立的 MySQL 資料庫。

* **打開檔案：** 再次確認simple-invoice-system/config/database.php。  
* **填寫資訊：** 再次確認 MySQL 填寫正確的設定。

太棒了！您已經成功安裝 XAMPP 並啟動了 Apache 伺服器，這意味著您的 PHP 運行環境已經準備就緒。

**4\. 運行專案**

* **開啟瀏覽器：** 打開您喜歡的網頁瀏覽器。  
* **輸入網址：** 輸入以下網址：  
* http://localhost/simple-invoice-system/

如果一切順利，您應該會看到：

1. **發票列表頁 (index.php)** 顯示您之前插入的兩筆範例資料。  
2. 如果您看到錯誤訊息，通常是 **"資料庫連線失敗..."**，請回到步驟 1 和 3 檢查您的 MySQL 服務狀態和連線設定。

---
# 🚀 專案優化與進階功能

開始從以下三個方面進行優化，它們將結合我們目前使用的 **純 PHP \+ 純 JavaScript** 技術棧：

---

## 🌟 實作優化一：即時計算總金額 (JavaScript)

我們將修改 create.php 和 edit.php 頁面，並在 js/main.js 中加入邏輯。

#### 步驟 A: 修改 js/main.js：新增一個函式來處理計算邏輯。  
```
　　　　　　　　　　function calculateTotalAmount() {}
```
#### 步驟 B: 修改 create.php 和 edit.php

在這兩個檔案中，找到「金額 (未稅)」欄位之後，新增一個顯示總金額的地方，並給予一個 ID 供 JavaScript 操作。

1. 修改 create.php：找到這段程式碼：
```
<div class="form-group">
    <label for="amount">金額 (未稅) <span style="color: red;">*</span></label>
    <input type="number" step="0.01" id="amount" name="amount" required min="0.01">
</div>
```

2. 修改 edit.php：找到顯示「總金額 (含稅)」的段落：

```
<p><strong>總金額 (含稅):</strong> $<?php echo number_format($invoice['total_amount'], 2); ?></p>
```
  

### 測試結果
#### 現在，當您進入新增或編輯頁面，並在「金額 (未稅)」欄位輸入數字時，下方的「預估總金額 (含稅 5%)」應該會**立即**更新。
---
## 🌟 實作優化二：產生發票-實作「發票產生器」
#### "產生發票", 產生時, 可以選擇  
1\. 傳統三聯式發票  
2\. 電子計算機發票  
3\. 電子三聯式發票

---
### 「產生發票 (發票列印或檢視)」功能需要兩個核心步驟： 
<ul>
<li>前端選擇： 在列表頁或編輯頁增加一個「產生發票」按鈕，並彈出一個選擇視窗或導向一個新的頁面讓使用者選擇發票格式。 
<li>後端排版： 根據選擇的格式（三聯式或電子計算機發票），使用 PHP 讀取資料庫數據，並用 HTML/CSS 進行特定版面的排版。 我們將新增一個檔案 generate.php 來處理此邏輯。 
</ul>

---
### 階段一：新增發票格式選擇功能

#### **1\. 修改發票列表頁 (index.php)**

在列表頁的操作欄位中，為每筆發票新增一個「產生」按鈕。

#### **2\. 修改 JavaScript 檔案 (js/main.js)**

新增 promptGenerate 函式，用於提示使用者選擇發票格式。

### 
---
### 階段二：建立發票產生器 (generate.php)

#### 這個頁面負責接收 ID 和格式參數，從資料庫讀取數據，並根據選擇的格式輸出 HTML 排版。
---
### 階段三：建立專屬樣式檔案

雖然我們在 generate.php 中使用了內聯樣式，但為了展示專業性，我們可以為不同的格式準備樣式檔案。

#### 1\. 建立 css/triplicate.css (三聯式發票樣式)

#### 2\. 建立 css/computer.css (電子計算機發票樣式)

#### 3\.建立 css/e\_triplicate.css (電子三聯式發票樣式):為了與其他兩種格式區分，我們可以讓它看起來更現代或更具「證明聯」的特性。
---
### 總結

現在，當使用者在發票列表頁點擊「產生」按鈕時，將會看到三個選項：

1. **傳統三聯式發票** (format=triplicate)  
2. **電子計算機發票** (format=computer)  
3. **電子三聯式發票** (format=e\_triplicate)

這三種格式的發票都可以根據資料庫數據正確生成，並有各自的樣式文件。

---
## 🌟 實作優化三： 分頁與搜尋功能
此功能主要涉及對 index.php 和資料庫查詢（PHP/MySQL）的修改。

### 階段一：規劃分頁參數 (PHP)
我們需要定義每頁顯示的記錄數，並根據 URL 參數計算偏移量。
修改 index.php (頂部 PHP 邏輯): 取代 index.php 最上方的 PHP 區塊。

### 階段二：新增搜尋表單 (HTML)
在 index.php 的 HTML 部分，我們在列表上方新增一個搜尋表單。
修改 index.php (HTML 區塊)

### 總結
資料庫查詢 (index.php PHP 區塊): 現在的 SQL 查詢使用了 WHERE...LIKE 進行搜尋，並使用了 LIMIT :offset, :limit 進行分頁。

前端 (index.php HTML 區塊): 顯示了搜尋表單和分頁導覽。

URL 參數處理: 分頁連結和表單提交會同時處理 page 和 search 兩個 URL 參數，確保搜尋結果能夠被正確分頁。

現在，您可以在頁面上方輸入發票號碼或客戶名稱進行模糊搜尋，並且如果記錄超過 10 筆，下方就會出現分頁導覽。

<b><u>備註:修改分頁顯示筆數, 日後可設定在config.php中, 以後只要改設定檔即可</u></b>

``` 
index.php 最上方

     $records_per_page = 5; // <--- 將這裡的值從 10 修改為 5
```

## 🌟實作優化四: 排序功能
  排序功能主要涉及三個步驟：

<ol>
<li>URL 參數控制： 處理 sort (排序欄位) 和 order (排序方向) 兩個新的 URL 參數。</li>

<li>PHP/MySQL 查詢： 根據參數修改 SQL 查詢中的 ORDER BY 子句。</li>

<li>前端介面： 修改表格標題，使其可以點擊來切換排序。</li>
</ol>

### 階段一：修改 index.php 頂部 PHP 邏輯
我們將在分頁和搜尋邏輯的基礎上，加入排序邏輯。

取代 index.php 最上方的 PHP 區塊：

### 階段二：新增表格標題排序連結 (HTML/PHP)

我們將修改表格的 <th> 標籤，使其包含一個連結，點擊後可以切換排序欄位或方向。
在 index.php 的 HTML 區塊中，找到發票表格的標題部分：將其替換為帶有排序連結的程式碼

### 階段三：修改分頁連結 (保留排序參數)
#### 最後，在 `index.php` 下方的分頁導覽區塊，我們需要確保分頁連結在跳頁時能保留當前的排序設定。將其替換為，以便同時保留搜尋詞和排序參數。
---
### 測試結果

<ol>
  <li>重新整理 index.php 頁面。
  <li>點擊表格標題（發票號碼、日期、客戶名稱、金額）。
  <li>資料將會依該欄位排序，並且標題旁會顯示 ▲ (升序) 或 ▼ (降序) 箭頭。
  <li>連續點擊同一欄位，排序方向會自動切換。
</ol>

### 排序和搜尋功能現在可以完美地一起運作囉！

---
---
# 備註: 台灣發票格式說明
## 二聯式和三聯式發票的主要區別在於開立對象和發票張數。 
<ul>
<li>二聯式發票：一般個人消費者，金額為含稅，共兩聯：存根聯（開立人保留）和收執聯（客戶收執）。 
<li>聯式發票：有統一編號的營業人，金額為未稅價、稅額、總計，共三聯：存根聯、扣抵聯（客戶申報扣抵稅額用）和收執聯。
</ul>

三聯式發票有電子發票形式，現在企業已普遍採用，不再開立紙本「電子計算機發票」。電子化三聯式發票通常會將電子檔透過電子郵件寄送給買方，或提供線上載具連結供買方下載列印，方便企業申報稅務。  

## 電子三聯式發票的運作方式 
<ul>
<li>開立與傳輸

  營業人透過電子發票系統開立三聯式電子發票，發票資訊會即時傳輸至財政部的電子發票整合服務平台。 
<li>通知與下載

  發票開立後，系統會將發票資訊以電子郵件通知買方，其中包含一個線上連結，讓買方可以自行下載PDF檔。 
<li>記帳與申報

  買方可自行列印下載的電子檔，作為報帳或申報扣抵營業稅的憑證。 
<li>系統支援

  為配合電子發票的電子化趨勢，許多企業（如飯店、車商等）已提前轉換，改用電子發票系統取代傳統紙本發票。 
</ul>

 ## 過去與現在的差別 
 <ol>
 <li>傳統三聯式發票

 從前是由電腦開立後再列印出紙本發票，一式三聯，供買賣雙方留存及申報。 
 <li>電子計算機發票

 過去一種電子計算機發票形式，也稱為三聯式發票，但財政部已於2021年1月1日起全面停止使用。 
 <li>電子三聯式發票

 現在企業開立的，是以電子檔儲存並傳輸到財政部電子發票整合服務平台，買方可依需求下載列印。 
 </ol>

 ---
 ---


