<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Абсолютный путь к папке загрузок
$upload_dir = __DIR__ . '/../uploads/';

// Создаем папку если не существует
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Не удалось создать папку uploads']);
        exit;
    }
}

// Проверяем, что файл был отправлен
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'Файл не был отправлен']);
    exit;
}

$file = $_FILES['image'];

// Проверяем ошибки загрузки
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        1 => 'Файл превышает максимальный размер',
        2 => 'Файл слишком большой',
        3 => 'Файл загружен не полностью',
        4 => 'Файл не был выбран',
        6 => 'Отсутствует временная папка',
        7 => 'Ошибка записи на диск',
        8 => 'Расширение PHP остановило загрузку'
    ];
    
    echo json_encode(['success' => false, 'error' => $error_messages[$file['error']] ?? 'Неизвестная ошибка: ' . $file['error']]);
    exit;
}

// Проверяем тип файла
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'error' => 'Недопустимый тип файла. Разрешены: ' . implode(', ', $allowed_extensions)]);
    exit;
}

// Проверяем MIME тип
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$mime_type = mime_content_type($file['tmp_name']);

if (!in_array($mime_type, $allowed_mimes)) {
    echo json_encode(['success' => false, 'error' => 'Недопустимый тип содержимого: ' . $mime_type]);
    exit;
}

// Проверяем размер файла (5MB максимум)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'error' => 'Файл слишком большой. Максимум: 5MB']);
    exit;
}

// Генерируем уникальное имя файла
$filename = uniqid() . '.' . $extension;
$target_path = $upload_dir . $filename;

// Перемещаем файл
if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Возвращаем URL относительно корня сайта
    $image_url = '/uploads/' . $filename;
    echo json_encode(['success' => true, 'url' => $image_url]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при сохранении файла']);
}
?>