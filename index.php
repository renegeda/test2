<?php
require_once 'includes/functions.php';
$articles = getAllArticles();
?>

<?php include 'includes/header.php'; ?>
<h1 class="mb-4">Последние статьи</h1>

<div class="row">
    <?php foreach ($articles as $article): ?>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                <p class="card-text"><?php echo truncateText($article['short_description'] ?: $article['content']); ?></p>
                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">Читать далее</a>
            </div>
            <div class="card-footer text-muted">
                <?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($articles)): ?>
<div class="alert alert-info">Статей пока нет.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>