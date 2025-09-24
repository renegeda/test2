<?php
require_once '../includes/functions.php';

// Обработка действий
if ($_POST) {
    // Добавление категории
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if ($name && $slug) {
            if (addCategory($name, $slug, $description)) {
                header('Location: categories.php?message=added');
                exit;
            }
        }
    }
    
    // Редактирование категории
    if (isset($_POST['edit_category'])) {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if ($id && $name && $slug) {
            if (updateCategory($id, $name, $slug, $description)) {
                header('Location: categories.php?message=updated');
                exit;
            }
        }
    }
    
    // Удаление категории
    if (isset($_POST['delete_selected'])) {
        if (!empty($_POST['selected_category'])) {
            $category_id = $_POST['selected_category'];
            if (deleteCategory($category_id)) {
                header('Location: categories.php?message=deleted');
                exit;
            }
        }
    }
}

$categories = getCategoriesWithArticlesCount();
$title = 'Управление категориями';
include '../includes/header.php';
?>

<h1>Управление категориями</h1>

<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php
        $messages = [
            'added' => 'Категория успешно добавлена!',
            'updated' => 'Категория успешно обновлена!',
            'deleted' => 'Категория успешно удалена!'
        ];
        echo $messages[$_GET['message']] ?? 'Операция выполнена!';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo isset($_GET['edit']) ? 'Редактировать категорию' : 'Добавить категорию'; ?></h5>
            </div>
            <div class="card-body">
                <?php
                $edit_category = null;
                if (isset($_GET['edit'])) {
                    $edit_category = getCategoryById($_GET['edit']);
                }
                ?>
                <form method="POST">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Название категории *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $edit_category['name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL) *</label>
                        <input type="text" class="form-control" id="slug" name="slug" 
                               value="<?php echo $edit_category['slug'] ?? ''; ?>" required>
                        <small class="text-muted">Латинские буквы, цифры, дефисы</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_category['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <?php if ($edit_category): ?>
                            <button type="submit" name="edit_category" class="btn btn-primary">💾 Сохранить изменения</button>
                            <a href="categories.php" class="btn btn-secondary">❌ Отмена</a>
                        <?php else: ?>
                            <button type="submit" name="add_category" class="btn btn-success">➕ Добавить категорию</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <form method="POST" id="categories-form">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Список категорий</h5>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                        Снять выделение
                    </button>
                    <button type="submit" name="delete_selected" class="btn btn-danger btn-sm" id="delete-selected" disabled>
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
                            <th>Название</th>
                            <th>Slug</th>
                            <th>Статей</th>
                            <th>Дата создания</th>
                            <th width="120">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Категории пока не добавлены
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <input type="radio" name="selected_category" value="<?php echo $category['id']; ?>" 
                                           class="category-radio">
                                </td>
                                <td><?php echo $category['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    <?php if ($category['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($category['slug']); ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $category['articles_count'] > 0 ? 'primary' : 'secondary'; ?>">
                                        <?php echo $category['articles_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y', strtotime($category['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="categories.php?edit=<?php echo $category['id']; ?>" 
                                           class="btn btn-outline-primary" title="Редактировать">
                                            ✏️
                                        </a>
                                        <a href="../category.php?slug=<?php echo $category['slug']; ?>" 
                                           target="_blank" class="btn btn-outline-info" title="Просмотреть">
                                            👁️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deselectAllBtn = document.getElementById('deselect-all');
    const categoryRadios = document.querySelectorAll('.category-radio');
    const deleteSelectedBtn = document.getElementById('delete-selected');

    function updateSelection() {
        const selectedCategory = document.querySelector('.category-radio:checked');
        deleteSelectedBtn.disabled = !selectedCategory;
        
        if (selectedCategory) {
            const row = selectedCategory.closest('tr');
            const name = row.querySelector('td:nth-child(3) strong').textContent;
            const articlesCount = row.querySelector('.badge').textContent;
            deleteSelectedBtn.setAttribute('data-name', name);
            deleteSelectedBtn.setAttribute('data-count', articlesCount);
        }
    }

    deselectAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        categoryRadios.forEach(radio => {
            radio.checked = false;
        });
        updateSelection();
    });

    categoryRadios.forEach(radio => {
        radio.addEventListener('change', updateSelection);
    });

    deleteSelectedBtn.addEventListener('click', function(e) {
        const selectedCategory = document.querySelector('.category-radio:checked');
        if (!selectedCategory) {
            e.preventDefault();
            alert('Выберите категорию для удаления!');
            return false;
        }
        
        const name = this.getAttribute('data-name');
        const count = this.getAttribute('data-count');
        
        if (count > 0) {
            return confirm(`Категория "${name}" содержит ${count} статей. Они останутся без категории. Удалить категорию?`);
        } else {
            return confirm(`Удалить категорию "${name}"?`);
        }
    });

    // Автогенерация slug из названия
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.value === '<?php echo $edit_category['slug'] ?? ''; ?>') {
                const slug = this.value.toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                slugInput.value = slug;
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>