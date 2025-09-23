<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Тест загрузки файлов</h1>";

// Проверяем права на папку uploads
$upload_dir = '../uploads/';
echo "<h2>1. Проверка папки uploads</h2>";
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✓ Папка uploads создана<br>";
    } else {
        echo "✗ Не удалось создать папку uploads<br>";
    }
} else {
    echo "✓ Папка uploads существует<br>";
}

echo "Права папки: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";

// Проверяем возможность записи
if (is_writable($upload_dir)) {
    echo "✓ Папка доступна для записи<br>";
} else {
    echo "✗ Папка НЕ доступна для записи<br>";
}

// Проверяем настройки PHP для загрузки файлов
echo "<h2>2. Настройки PHP</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "<br>";

// Простая форма для тестирования
echo '
<h2>3. Тестовая форма загрузки</h2>
<form action="test_upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="test_file">
    <input type="submit" value="Тестировать загрузку">
</form>';

if ($_FILES) {
    echo "<h3>Результат загрузки:</h3>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        $temp_file = $_FILES['test_file']['tmp_name'];
        $target_file = $upload_dir . 'test_' . uniqid() . '.txt';
        
        if (move_uploaded_file($temp_file, $target_file)) {
            echo "✓ Файл успешно загружен: " . $target_file . "<br>";
            unlink($target_file); // Удаляем тестовый файл
        } else {
            echo "✗ Ошибка перемещения файла<br>";
        }
    } else {
        echo "Код ошибки: " . $_FILES['test_file']['error'] . "<br>";
        $upload_errors = [
            0 => 'Нет ошибок',
            1 => 'Файл превышает upload_max_filesize',
            2 => 'Файл превышает MAX_FILE_SIZE в форме',
            3 => 'Файл загружен не полностью',
            4 => 'Файл не был загружен',
            6 => 'Отсутствует временная папка',
            7 => 'Не удалось записать файл на диск',
            8 => 'Расширение PHP остановило загрузку'
        ];
        echo "Ошибка: " . ($upload_errors[$_FILES['test_file']['error']] ?? 'Неизвестная ошибка');
    }
}
?>