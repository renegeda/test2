<?php
require_once '../includes/functions.php';

if ($_POST && isset($_POST['delete_id'])) {
    deleteArticle($_POST['delete_id']);
}

$articles = getAllArticles();
?>

<?php 
$title = 'Админка - Список статей';
include '../includes/header.php'; 
?>

<h1>Управление статьями</h1>
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