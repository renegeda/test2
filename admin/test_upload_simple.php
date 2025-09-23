<?php
echo '<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_image">
    <input type="submit" value="Тест загрузки">
</form>';

if ($_FILES) {
    echo '<pre>';
    print_r($_FILES);
    
    if ($_FILES['test_image']['error'] === 0) {
        $temp_file = $_FILES['test_image']['tmp_name'];
        $target_file = '../uploads/test_' . uniqid() . '.jpg';
        
        if (move_uploaded_file($temp_file, $target_file)) {
            echo "✓ Файл загружен: $target_file";
        } else {
            echo "✗ Ошибка перемещения файла";
        }
    }
    echo '</pre>';
}
?>