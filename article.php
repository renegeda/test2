<?php
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$article = getArticleById($_GET['id']);
if (!$article) {
    header('Location: index.php');
    exit;
}

// Увеличиваем счетчик просмотров
incrementArticleViews($article['id']);

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$article = getArticleById($_GET['id']);
if (!$article) {
    header('Location: index.php');
    exit;
}
?>

<?php 
$title = htmlspecialchars($article['title']);
include 'includes/header.php'; 
?>

<article>
    <h1><?php echo $title; ?></h1>
    <p class="text-muted">Опубликовано: <?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></p>
    
<div class="article-content">
    <?php echo getArticleContent($article); ?>
</div>
</article>

<div class="mt-4">
    <a href="index.php" class="btn btn-secondary">← Назад к списку</a>
    <a href="admin/edit.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">Редактировать</a>
</div>

<?php include 'includes/footer.php'; ?>