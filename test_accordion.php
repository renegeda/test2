<?php
require_once 'includes/functions.php';

$test_content = 'Текст до аккордеона

[[accordion]]
Заголовок 1::Текст для первого элемента аккордеона
Заголовок 2::Текст для второго элемента
Заголовок 3::Текст для третьего элемента
[[/accordion]]

Текст после аккордеона';

echo "<h1>Тест парсинга аккордеона</h1>";
echo "<h2>Исходный текст:</h2>";
echo "<pre>" . htmlspecialchars($test_content) . "</pre>";

echo "<h2>Результат обработки:</h2>";
echo processAccordions($test_content);

// Тестируем функцию напрямую
echo "<h2>Отладочная информация:</h2>";
$pattern = '/\[\[accordion\]\](.*?)\[\[\/accordion\]\]/s';
preg_match($pattern, $test_content, $matches);

if (isset($matches[1])) {
    echo "<h3>Содержимое аккордеона:</h3>";
    echo "<pre>" . htmlspecialchars($matches[1]) . "</pre>";
    
    $lines = explode("\n", trim($matches[1]));
    echo "<h3>Разбивка на строки:</h3>";
    foreach ($lines as $i => $line) {
        echo "Строка $i: \"" . htmlspecialchars(trim($line)) . "\"<br>";
    }
}
?>