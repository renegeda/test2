<?php
require_once 'includes/functions.php';
$articles = getAllArticles();
?>

<?php include 'includes/header.php'; ?>
<h1 class="mb-4">Последние статьи</h1>

<div class="row">
    <?php foreach ($articles as $article): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <!-- Превью изображение -->
            <?php if (!empty($article['preview_image'])): ?>
            <img src="<?php echo htmlspecialchars($article['preview_image']); ?>" 
                 class="card-img-top" alt="<?php echo htmlspecialchars($article['title']); ?>"
                 onerror="this.style.display='none'">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                <span class="text-muted">Нет изображения</span>
            </div>
            <?php endif; ?>
            
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                <p class="card-text flex-grow-1"><?php echo truncateText($article['short_description'] ?: $article['content']); ?></p>
                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary mt-auto">Читать далее</a>
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