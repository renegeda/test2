<?php
require_once 'config/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Создание таблицы статей
$sql = "CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    short_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

$pdo->exec($sql);

// Добавляем тестовые данные
$test_articles = [
    [
        'title' => 'Первая статья с аккордеоном',
        'short_description' => 'Статья демонстрирующая работу аккордеона в тексте',
        'content' => 'Это пример статьи где мы покажем как работает аккордеон.

[[accordion]]
Как установить Quill?::Для установки Quill нужно подключить CSS и JS файлы через CDN или установить через npm.
Как работает аккордеон?::Аккордеон создается через специальные шорткоды в тексте редактора.
Где найти документацию?::Документация доступна на официальном сайте Bootstrap и Quill.
[[/accordion]]

После аккордеона можно продолжить обычный текст статьи.'
    ],
    [
        'title' => 'Вторая статья о возможностях системы',
        'short_description' => 'Обзор функционала редактора и шорткодов',
        'content' => 'В этой статье мы рассмотрим различные возможности системы.

[[accordion]]
Редактирование статей::Для редактирования перейдите в админ-панель и выберите нужную статью.
Форматирование текста::Используйте панель инструментов Quill для форматирования.
Вставка медиа::Поддерживается вставка изображений и видео.
[[/accordion]]

Не забывайте сохранять изменения после редактирования.'
    ]
];

foreach ($test_articles as $article) {
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, short_description) VALUES (?, ?, ?)");
    $stmt->execute([$article['title'], $article['content'], $article['short_description']]);
}

echo "База данных инициализирована успешно!";
?>