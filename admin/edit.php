<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$article = getArticleById($_GET['id']);
if (!$article) {
    header('Location: index.php');
    exit;
}

if ($_POST) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    
    if ($title && $content) {
        if (updateArticle($article['id'], $title, $content, $short_description)) {
            header('Location: index.php');
            exit;
        }
    }
}

$title = 'Редактировать статью';
include __DIR__ . '/../includes/header.php';
?>

<h1>Редактировать статью</h1>

<form id="article-form" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="title" class="form-label">Заголовок</label>
        <input type="text" class="form-control" id="title" name="title" 
               value="<?php echo htmlspecialchars($article['title']); ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="short_description" class="form-label">Краткое описание</label>
        <textarea class="form-control" id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($article['short_description']); ?></textarea>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Содержание</label>
        <div id="editor" style="height: 300px;"><?php echo $article['content']; ?></div>
        <input type="hidden" name="content" id="content-input">
    </div>
    
    <div class="mb-3">
        <strong>Шорткод для аккордеона (в одну строку через |):</strong><br>
        <code>[[accordion]]Заголовок 1::Текст 1|Заголовок 2::Текст 2|Заголовок 3::Текст 3[[/accordion]]</code>
        
        <div class="mt-2">
            <small class="text-muted">
                <strong>Пример использования:</strong><br>
                В редакторе пишите аккордеон в одну строку, разделяя элементы через |<br>
                Каждый элемент: <code>Заголовок::Текст</code>
            </small>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="index.php" class="btn btn-secondary">Отмена</a>
</form>

<!-- Подключаем Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
// Инициализация Quill редактора
var quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'font': [] }, { 'size': [] }],
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            ['link', 'image', 'video'],
            ['clean']
        ]
    }
});

// Обработчик загрузки изображений
quill.getModule('toolbar').addHandler('image', function() {
    selectLocalImage();
});

function selectLocalImage() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    
    input.onchange = function() {
        const file = input.files[0];
        if (!file) return;
        
        // Показываем индикатор загрузки
        const range = quill.getSelection();
        const loadingText = '🔄 Загрузка...';
        quill.insertText(range.index, loadingText);
        
        const formData = new FormData();
        formData.append('image', file);
        
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            return response.json();
        })
        .then(result => {
            // Удаляем индикатор загрузки
            quill.deleteText(range.index, loadingText.length);
            
            if (result.success) {
                // Вставляем изображение
                quill.insertEmbed(range.index, 'image', result.url);
                // Перемещаем курсор после изображения
                quill.setSelection(range.index + 1, 0);
            } else {
                alert('Ошибка: ' + result.error);
            }
        })
        .catch(error => {
            // Удаляем индикатор загрузки
            quill.deleteText(range.index, loadingText.length);
            alert('Ошибка загрузки: ' + error.message);
        });
    };
}

// Сохранение содержимого перед отправкой формы
document.getElementById('article-form').onsubmit = function() {
    const contentInput = document.getElementById('content-input');
    contentInput.value = quill.root.innerHTML;
    return true;
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>