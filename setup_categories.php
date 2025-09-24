<?php
// setup_categories_simple.php - максимально упрощенная версия
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Настройка категорий - Упрощенная версия</h1>";

try {
    // Простое подключение к SQLite
    $pdo = new PDO('sqlite:news.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Подключение к SQLite успешно!<br>";
    
    // Создаем таблицу categories
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Таблица 'categories' создана/проверена<br>";
    
    // Проверяем поле category_id в articles
    $stmt = $pdo->query("PRAGMA table_info(articles)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasCategoryId = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'category_id') {
            $hasCategoryId = true;
            break;
        }
    }
    
    if (!$hasCategoryId) {
        $pdo->exec("ALTER TABLE articles ADD COLUMN category_id INTEGER DEFAULT NULL");
        echo "✅ Поле 'category_id' добавлено в articles<br>";
    } else {
        echo "✅ Поле 'category_id' уже существует<br>";
    }
    
    echo "<h2>✅ Настройка завершена успешно!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Ошибка:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Дополнительная информация
    echo "<h3>Дополнительная информация:</h3>";
    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "PDO SQLite Driver: " . (in_array('sqlite', PDO::getAvailableDrivers()) ? 'Доступен' : 'Не доступен') . "<br>";
    echo "Рабочая директория: " . getcwd() . "<br>";
}
?>