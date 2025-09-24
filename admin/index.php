<?php
require_once '../includes/functions.php';

// Уведомления об успешных операциях
if (isset($_GET['message'])) {
    $messages = [
        'added' => 'Статья успешно добавлена!',
        'updated' => 'Статья успешно обновлена!', 
        'deleted' => 'Статья успешно удалена!'
    ];
    
    if (isset($messages[$_GET['message']])) {
        $alert_message = $messages[$_GET['message']];
        
        // Очищаем URL от параметра message после показа уведомления
        echo '<script>
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete("message");
                window.history.replaceState({}, document.title, url.toString());
            }
        </script>';
    }
}

// Обработка удаления выбранной статьи
if ($_POST && isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_article'])) {
        $article_id = $_POST['selected_article'];
        if (deleteArticle($article_id)) {
            header('Location: index.php?message=deleted');
            exit;
        }
    } else {
        $alert_message = 'Выберите статью для удаления!';
    }
}

// Обработка редактирования выбранной статьи
if ($_POST && isset($_POST['edit_selected'])) {
    if (!empty($_POST['selected_article'])) {
        $article_id = $_POST['selected_article'];
        header("Location: edit.php?id=$article_id");
        exit;
    } else {
        $alert_message = 'Выберите статью для редактирования!';
    }
}

$articles = getAllArticles();
?>

<?php 
$title = 'Админка - Список статей';
include '../includes/header.php'; 
?>

<h1>Управление статьями</h1>

<?php if (isset($alert_message)): ?>
<div class="alert alert-<?php echo strpos($alert_message, 'успешно') !== false ? 'success' : 'danger'; ?> alert-dismissible fade show">
    <?php echo $alert_message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Остальной код без изменений -->
<form method="POST" id="articles-form">
    <!-- Панель управления -->
    <div class="d-flex justify-content-between align-items-center my-5">
        <div>
            <a href="add.php" class="btn btn-success">+ Добавить статью</a>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary" id="deselect-all">
                Снять выделение
            </button>
            <button type="submit" name="edit_selected" class="btn btn-primary mx-2" id="edit-selected" disabled>
                ✏️ Редактировать выбранную
            </button>
            <button type="submit" name="delete_selected" class="btn btn-danger" id="delete-selected" disabled>
                🗑️ Удалить выбранную
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="40">Выбор</th>
                    <th>ID</th>
                    <th>Превью</th>
                    <th>Заголовок</th>
                    <th>Дата</th>
                    <th>Краткое описание</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Статьи пока не добавлены
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <input type="radio" name="selected_article" value="<?php echo $article['id']; ?>" 
                                   class="article-radio">
                        </td>
                        <td><?php echo $article['id']; ?></td>
                        <td>
                            <?php if (!empty($article['preview_image'])): ?>
                            <img src="<?php echo htmlspecialchars($article['preview_image']); ?>" 
                                 style="height: 50px; width: 50px; object-fit: cover; border-radius: 3px;"
                                 alt="Превью"
                                 onerror="this.style.display='none'">
                            <?php else: ?>
                            <span class="text-muted">Нет</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                        </td>
                        <td><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></td>
                        <td>
                            <?php 
                            $short_desc = $article['short_description'] ?? '';
                            echo htmlspecialchars(mb_strlen($short_desc) > 100 ? mb_substr($short_desc, 0, 100) . '...' : $short_desc);
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deselectAllBtn = document.getElementById('deselect-all');
    const articleRadios = document.querySelectorAll('.article-radio');
    const editSelectedBtn = document.getElementById('edit-selected');
    const deleteSelectedBtn = document.getElementById('delete-selected');

    // Обновление состояния кнопок
    function updateSelection() {
        const selectedArticle = document.querySelector('.article-radio:checked');
        const hasSelection = !!selectedArticle;
        
        editSelectedBtn.disabled = !hasSelection;
        deleteSelectedBtn.disabled = !hasSelection;
        
        if (hasSelection) {
            const row = selectedArticle.closest('tr');
            const title = row.querySelector('td:nth-child(4) strong').textContent;
            deleteSelectedBtn.setAttribute('data-title', title);
            editSelectedBtn.setAttribute('data-title', title);
        }
    }

    // Кнопка "Снять выделение"
    deselectAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        articleRadios.forEach(radio => {
            radio.checked = false;
        });
        updateSelection();
    });

    // Отслеживание изменений радио-кнопок
    articleRadios.forEach(radio => {
        radio.addEventListener('change', updateSelection);
    });

    // Подтверждение удаления
    deleteSelectedBtn.addEventListener('click', function(e) {
        const selectedArticle = document.querySelector('.article-radio:checked');
        if (!selectedArticle) {
            e.preventDefault();
            alert('Выберите статью для удаления!');
            return false;
        }
        
        const title = this.getAttribute('data-title');
        return confirm(`Удалить статью "${title}"?`);
    });

    // Обработка редактирования
    editSelectedBtn.addEventListener('click', function(e) {
        const selectedArticle = document.querySelector('.article-radio:checked');
        if (!selectedArticle) {
            e.preventDefault();
            alert('Выберите статью для редактирования!');
            return false;
        }
        return true;
    });

    // Двойной клик по строке для быстрого редактирования
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('dblclick', function() {
            const radio = this.querySelector('.article-radio');
            if (radio) {
                radio.checked = true;
                updateSelection();
                editSelectedBtn.click();
            }
        });
        
        // Подсветка строки при наведении
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>