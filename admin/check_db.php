<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h1>Проверка базы данных</h1>";
    
    // Проверяем существование таблицы
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
    echo "<h2>Таблицы в базе:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table['name'] . "</li>";
    }
    echo "</ul>";
    
    // Проверяем статьи
    $articles = $pdo->query("SELECT COUNT(*) as count FROM articles")->fetch();
    echo "<h2>Статей в базе: " . $articles['count'] . "</h2>";
    
    if ($articles['count'] > 0) {
        $articles_list = $pdo->query("SELECT id, title, created_at FROM articles ORDER BY created_at DESC")->fetchAll();
        echo "<table class='table'>";
        echo "<tr><th>ID</th><th>Заголовок</th><th>Дата</th></tr>";
        foreach ($articles_list as $article) {
            echo "<tr>";
            echo "<td>" . $article['id'] . "</td>";
            echo "<td>" . htmlspecialchars($article['title']) . "</td>";
            echo "<td>" . $article['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>