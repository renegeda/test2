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
    }
}

// Обработка удаления статьи
if ($_POST && isset($_POST['delete_id'])) {
    if (deleteArticle($_POST['delete_id'])) {
        header('Location: index.php?message=deleted');
        exit;
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
<div class="alert alert-success alert-dismissible fade show">
    <?php echo $alert_message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<a href="add.php" class="btn btn-success mb-3">+ Добавить статью</a>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
            <tr>
                <td><?php echo $article['id']; ?></td>
                <td>
                    <?php if (!empty($article['preview_image'])): ?>
                    <img src="<?php echo htmlspecialchars($article['preview_image']); ?>" 
                         style="height: 100px; width: 100px; object-fit: cover; border-radius: 3px; margin: 0 auto;"
                         alt="Превью"
                         onerror="this.style.display='none'">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($article['title']); ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить статью?')">
                        <input type="hidden" name="delete_id" value="<?php echo $article['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>