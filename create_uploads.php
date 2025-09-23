<?php
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "Папка uploads создана успешно!";
        
        // Создаем .htaccess для защиты
        file_put_contents($upload_dir . '.htaccess', 
            "Order Deny,Allow\nDeny from all\n<FilesMatch '\.(jpg|jpeg|png|gif|webp)$'>\nAllow from all\n</FilesMatch>");
        echo "<br>.htaccess создан";
    } else {
        echo "Ошибка создания папки!";
    }
} else {
    echo "Папка uploads уже существует!";
    
    // Проверяем права
    echo "<br>Права папки: " . substr(sprintf('%o', fileperms($upload_dir)), -4);
    echo "<br>Доступна для записи: " . (is_writable($upload_dir) ? 'Да' : 'Нет');
}
?>