<?php
// test.php - простой тест
echo "✅ PHP работает!<br>";

// Проверяем подключение к БД
try {
    require_once 'config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    echo "✅ База данных подключена!<br>";
    
    // Проверяем существование news.db
    if (file_exists('news.db')) {
        echo "✅ Файл news.db существует<br>";
    } else {
        echo "❌ Файл news.db не найден<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка БД: " . $e->getMessage() . "<br>";
}

// Проверяем права доступа
echo "✅ Права на запись: " . (is_writable('.') ? 'Да' : 'Нет') . "<br>";
?>