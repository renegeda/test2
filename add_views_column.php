<?php
// add_views_column.php
require_once 'config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Подключение к БД успешно!<br>";
    
    // Проверяем существование поля views
    $stmt = $pdo->query("PRAGMA table_info(articles)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasViewsColumn = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'views') {
            $hasViewsColumn = true;
            break;
        }
    }
    
    if ($hasViewsColumn) {
        echo "✅ Поле 'views' уже существует в таблице articles<br>";
    } else {
        // Добавляем поле views
        $pdo->exec("ALTER TABLE articles ADD COLUMN views INTEGER DEFAULT 0");
        echo "✅ Поле 'views' успешно добавлено в таблицу articles<br>";
        
        // Проверяем добавление
        $stmt = $pdo->query("PRAGMA table_info(articles)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Обновленная структура таблицы articles:<br>";
        foreach ($columns as $col) {
            echo "- {$col['name']} ({$col['type']})<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>