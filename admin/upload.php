<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Простая проверка авторизации (в реальном проекте сделайте нормальную аутентификацию)
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    die('Forbidden');
}

if ($_FILES['image'] && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $file_type = $_FILES['image']['type'];
    $file_size = $_FILES['image']['size'];
    
    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        die('Invalid file type');
    }
    
    if ($file_size > $max_size) {
        http_response_code(400);
        die('File too large');
    }
    
    // Генерируем уникальное имя файла
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $upload_path = '../uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        // Возвращаем URL для Quill
        $image_url = '/uploads/' . $filename; // Измените на ваш реальный путь
        echo json_encode(['success' => true, 'url' => $image_url]);
    } else {
        http_response_code(500);
        die('Upload failed');
    }
} else {
    http_response_code(400);
    die('No file uploaded');
}
?>