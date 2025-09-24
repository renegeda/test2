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
    $preview_image = $_POST['preview_image'] ?? '';
    
    if ($title && $content) {
        // Если превью очистили, пытаемся взять первое изображение из контента
        if (empty($preview_image)) {
            $preview_image = extractFirstImage($content);
        }
        
        if (updateArticle($article['id'], $title, $content, $short_description, $preview_image)) {
            header('Location: index.php?message=updated');
            exit;
        }
    }
}

$title = 'Редактировать статью';
include __DIR__ . '/../includes/header.php';
?>

<h1>Редактировать статью</h1>

<form id="article-form" method="POST">
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
        <label for="preview_image" class="form-label">Превью-изображение</label>
        <div class="input-group">
            <input type="text" class="form-control" id="preview_image" name="preview_image" 
                   value="<?php echo htmlspecialchars($article['preview_image'] ?? ''); ?>"
                   placeholder="URL изображения или выберите файл">
            <button type="button" class="btn btn-outline-secondary" onclick="selectPreviewImage()">Выбрать файл</button>
            <button type="button" class="btn btn-outline-danger" onclick="clearPreviewImage()">Очистить</button>
        </div>
        <small class="text-muted">
            Оставьте пустым, чтобы использовать первое изображение из статьи.
        </small>
        
        <!-- Превью изображения -->
        <div id="preview-image-container" class="mt-2" style="<?php echo !empty($article['preview_image']) ? '' : 'display: none;'; ?>">
            <img id="preview-image" src="<?php echo htmlspecialchars($article['preview_image'] ?? ''); ?>" 
                 class="img-thumbnail" style="max-height: 150px;">
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Содержание</label>
        
        <!-- Кнопка переключения режима -->
        <div class="mb-2">
            <button type="button" id="html-toggle-btn" class="btn btn-outline-secondary html-toggle-btn">📄 HTML редактор</button>
        </div>
        
        <!-- Контейнер для редакторов -->
        <div id="editor-container">
            <div id="editor" style="height: 400px;"><?php echo $article['content']; ?></div>
            <textarea id="html-editor" class="form-control html-editor" style="display: none; height: 400px;"><?php echo htmlspecialchars($article['content']); ?></textarea>
        </div>
        <input type="hidden" name="content" id="content-input" value="<?php echo htmlspecialchars($article['content']); ?>">
    </div>
    
    <div class="mb-3">
        <strong>Шорткод для аккордеона (в одну строку через |):</strong><br>
        <code>[[accordion]]Заголовок 1::Текст 1|Заголовок 2::Текст 2|Заголовок 3::Текст 3[[/accordion]]</code>
    </div>
    
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="index.php" class="btn btn-secondary">Отмена</a>
</form>

<!-- Подключаем Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
// Глобальные переменные
let quill;
let isHtmlMode = false;

// Инициализация Quill
function initQuill() {
    quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'align': [] }],
                ['link', 'image'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });
    
    // Обработчик изменений в Quill
    quill.on('text-change', function() {
        if (!isHtmlMode) {
            updateHiddenField();
            autoExtractFirstImage();
        }
    });
}

// Обработчик загрузки изображений
function setupImageHandler() {
    quill.getModule('toolbar').addHandler('image', function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        
        input.onchange = function() {
            const file = input.files[0];
            if (!file) return;
            
            const range = quill.getSelection();
            const loadingText = '🔄 Загрузка...';
            quill.insertText(range.index, loadingText);
            
            const formData = new FormData();
            formData.append('image', file);
            
            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                quill.deleteText(range.index, loadingText.length);
                
                if (result.success) {
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1, 0);
                } else {
                    alert('Ошибка: ' + result.error);
                }
            })
            .catch(error => {
                quill.deleteText(range.index, loadingText.length);
                alert('Ошибка загрузки: ' + error.message);
            });
        };
    });
}

// Переключение между режимами
function setupHtmlToggle() {
    const toggleBtn = document.getElementById('html-toggle-btn');
    const visualEditor = document.getElementById('editor');
    const htmlEditor = document.getElementById('html-editor');
    
    toggleBtn.addEventListener('click', function() {
        if (isHtmlMode) {
            // Переключаемся в визуальный режим
            visualEditor.style.display = 'block';
            htmlEditor.style.display = 'none';
            
            // Обновляем Quill содержимым из HTML редактора
            quill.root.innerHTML = htmlEditor.value;
            
            toggleBtn.innerHTML = '📄 HTML редактор';
            toggleBtn.classList.remove('btn-primary');
            toggleBtn.classList.add('btn-outline-secondary');
            isHtmlMode = false;
        } else {
            // Переключаемся в HTML режим
            visualEditor.style.display = 'none';
            htmlEditor.style.display = 'block';
            
            // Обновляем HTML редактор содержимым из Quill
            htmlEditor.value = quill.root.innerHTML;
            
            toggleBtn.innerHTML = '📝 Визуальный редактор';
            toggleBtn.classList.remove('btn-outline-secondary');
            toggleBtn.classList.add('btn-primary');
            isHtmlMode = true;
        }
        
        updateHiddenField();
    });
}

// Обновление скрытого поля формы
function updateHiddenField() {
    const contentInput = document.getElementById('content-input');
    if (isHtmlMode) {
        contentInput.value = document.getElementById('html-editor').value;
    } else {
        contentInput.value = quill.root.innerHTML;
    }
}

// Обработчик отправки формы
function setupFormHandler() {
    document.getElementById('article-form').addEventListener('submit', function(e) {
        updateHiddenField();
        
        // Дополнительная валидация если нужно
        const content = document.getElementById('content-input').value;
        const title = document.getElementById('title').value;
        
        if (!title.trim()) {
            e.preventDefault();
            alert('Заголовок статьи не может быть пустым!');
            return false;
        }
        
        if (!content.trim()) {
            e.preventDefault();
            alert('Содержание статьи не может быть пустым!');
            return false;
        }
        
        return true;
    });
}

// Функция для выбора превью-изображения
function selectPreviewImage() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    
    input.onchange = function() {
        const file = input.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('image', file);
        
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('preview_image').value = result.url;
                updatePreviewImage(result.url);
            } else {
                alert('Ошибка загрузки: ' + result.error);
            }
        })
        .catch(error => {
            alert('Ошибка сети: ' + error.message);
        });
    };
}

// Функция очистки превью-изображения
function clearPreviewImage() {
    document.getElementById('preview_image').value = '';
    updatePreviewImage('');
}

// Функция обновления превью изображения
function updatePreviewImage(url) {
    const container = document.getElementById('preview-image-container');
    const img = document.getElementById('preview-image');
    
    if (url) {
        img.src = url;
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Автоматическое извлечение первого изображения из контента
function autoExtractFirstImage() {
    const previewImageInput = document.getElementById('preview_image');
    
    // Если превью уже установлено вручную, не перезаписываем
    if (previewImageInput.value.trim() !== '') {
        return;
    }
    
    const content = quill.root.innerHTML;
    const parser = new DOMParser();
    const doc = parser.parseFromString(content, 'text/html');
    const firstImg = doc.querySelector('img');
    
    if (firstImg && firstImg.src) {
        previewImageInput.value = firstImg.src;
        updatePreviewImage(firstImg.src);
    }
}

// Автоматическое обновление превью при изменении URL
document.getElementById('preview_image').addEventListener('input', function() {
    updatePreviewImage(this.value);
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initQuill();
    setupImageHandler();
    setupHtmlToggle();
    setupFormHandler();
    
    // Обработчик изменений в HTML редакторе
    document.getElementById('html-editor').addEventListener('input', function() {
        updateHiddenField();
        autoExtractFirstImage();
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>