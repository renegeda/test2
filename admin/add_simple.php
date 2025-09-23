<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_POST) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    
    if ($title && $content) {
        if (addArticle($title, $content, $short_description)) {
            header('Location: index.php?message=added');
            exit;
        }
    }
}
?>

<form method="POST">
    <div class="mb-3">
        <label for="title" class="form-label">Заголовок</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    
    <div class="mb-3">
        <label for="short_description" class="form-label">Краткое описание</label>
        <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
    </div>
    
    <div class="mb-3">
        <label for="content" class="form-label">Содержание</label>
        <textarea class="form-control" id="content" name="content" rows="10"></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Сохранить</button>
</form>