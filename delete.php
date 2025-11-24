<?php
// delete.php

// 引入資料庫連線
require_once 'config/database.php';

// 確保是 GET 請求，並有 ID 參數
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // 準備 SQL 刪除語句
        $sql = "DELETE FROM invoices WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        // 綁定參數並執行
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // 成功後導向列表頁
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        // 刪除失敗
        die("刪除發票失敗: " . $e->getMessage());
    }
} else {
    // 參數不正確，導回列表
    header('Location: index.php');
    exit;
}