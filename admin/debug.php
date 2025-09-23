<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Отладка подключений</h1>";

// Проверяем config/database.php
echo "<h2>1. Проверка config/database.php</h2>";
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    echo "✓ Файл database.php подключен<br>";
    
    try {
        $db = new Database();
        $pdo = $db->getConnection();
        echo "✓ Подключение к БД успешно<br>";
    } catch (Exception $e) {
        echo "✗ Ошибка БД: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ Файл database.php не найден<br>";
}

// Проверяем includes/functions.php
echo "<h2>2. Проверка includes/functions.php</h2>";
if (file_exists('../includes/functions.php')) {
    require_once '../includes/functions.php';
    echo "✓ Файл functions.php подключен<br>";
    
    // Проверяем функции
    try {
        $articles = getAllArticles();
        echo "✓ Функция getAllArticles() работает. Статей: " . count($articles) . "<br>";
    } catch (Exception $e) {
        echo "✗ Ошибка functions: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ Файл functions.php не найден<br>";
}

// Проверяем includes/header.php
echo "<h2>3. Проверка includes/header.php</h2>";
if (file_exists('../includes/header.php')) {
    echo "✓ Файл header.php существует<br>";
} else {
    echo "✗ Файл header.php не найден<br>";
}
?>