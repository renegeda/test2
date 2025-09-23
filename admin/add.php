<?php
require_once '../includes/functions.php';

if ($_POST) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    
    if ($title && $content) {
        if (addArticle($title, $content, $short_description)) {
            header('Location: index.php');
            exit;
        }
    }
}

$title = 'Добавить статью';
$quill_script = true; // Добавляем эту переменную для header.php
include '../includes/header.php';
?>

<h1>Добавить новую статью</h1>

<form id="article-form" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="title" class="form-label">Заголовок</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    
    <div class="mb-3">
        <label for="short_description" class="form-label">Краткое описание</label>
        <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Содержание</label>
        <div id="editor"></div>
        <input type="hidden" name="content">
    </div>
    
    <div class="mb-3">
        <strong>Шорткод для аккордеона:</strong><br>
        <code>[[accordion]]<br>Заголовок 1::Текст 1<br>Заголовок 2::Текст 2<br>[[/accordion]]</code>
    </div>
    
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="index.php" class="btn btn-secondary">Отмена</a>
</form>

<script>
// Инициализация Quill редактора
var quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: {
            container: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                ['link', 'image', 'blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ],
            handlers: {
                'image': imageHandler
            }
        }
    }
});

// Обработчик загрузки изображений
function imageHandler() {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    
    input.onchange = function() {
        var file = input.files[0];
        if (!file) return;
        
        var formData = new FormData();
        formData.append('image', file);
        
        // Показываем сообщение о загрузке
        var range = quill.getSelection();
        var loadingText = '[Загрузка изображения...]';
        quill.insertText(range.index, loadingText);
        
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            // Удаляем текст загрузки
            quill.deleteText(range.index, loadingText.length);
            
            if (result.success) {
                // Вставляем изображение
                quill.insertEmbed(range.index, 'image', result.url);
            } else {
                alert('Ошибка загрузки: ' + (result.error || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            quill.deleteText(range.index, loadingText.length);
            alert('Ошибка сети: ' + error.message);
        });
    };
}

// Сохранение содержимого перед отправкой формы
document.getElementById('article-form').onsubmit = function() {
    var contentInput = document.querySelector('input[name=content]');
    contentInput.value = quill.root.innerHTML;
    return true;
};
</script>

<?php include '../includes/footer.php'; ?>