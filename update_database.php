<?php
require_once 'config/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Добавляем поле для превью-изображения
try {
    $pdo->exec("ALTER TABLE articles ADD COLUMN preview_image TEXT");
    echo "Поле preview_image добавлено успешно!<br>";
} catch (Exception $e) {
    echo "Поле уже существует или ошибка: " . $e->getMessage() . "<br>";
}

// Обновляем существующие статьи (опционально)
$articles = $pdo->query("SELECT id, content FROM articles WHERE preview_image IS NULL")->fetchAll();

foreach ($articles as $article) {
    // Пытаемся найти первое изображение в контенте
    preg_match('/<img[^>]+src="([^">]+)"/', $article['content'], $matches);
    if (isset($matches[1])) {
        $preview_image = $matches[1];
        $stmt = $pdo->prepare("UPDATE articles SET preview_image = ? WHERE id = ?");
        $stmt->execute([$preview_image, $article['id']]);
        echo "Для статьи ID {$article['id']} установлено превью: $preview_image<br>";
    }
}

echo "Обновление базы завершено!";
?>