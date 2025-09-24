<?php
require_once 'includes/functions.php';

if (!isset($_GET['slug'])) {
    header('Location: index.php');
    exit;
}

$category = getCategoryBySlug($_GET['slug']);
if (!$category) {
    header('Location: index.php');
    exit;
}

$articles = getArticlesByCategory($category['id']);
$title = $category['name'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($category['name']); ?></h1>
            
            <?php if ($category['description']): ?>
                <div class="alert alert-light">
                    <?php echo htmlspecialchars($category['description']); ?>
                </div>
            <?php endif; ?>
            
            <p class="text-muted">
                Статей в категории: <strong><?php echo count($articles); ?></strong>
            </p>
            
            <?php if (empty($articles)): ?>
                <div class="alert alert-info">
                    В этой категории пока нет статей.
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($articles as $article): ?>
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($article['title']); ?></h5>
                                <small><?php echo date('d.m.Y', strtotime($article['created_at'])); ?></small>
                            </div>
                            <?php if ($article['short_description']): ?>
                                <p class="mb-1"><?php echo htmlspecialchars($article['short_description']); ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Все категории</h5>
                </div>
                <div class="card-body">
                    <?php
                    $all_categories = getCategoriesWithArticlesCount();
                    if (!empty($all_categories)):
                    ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($all_categories as $cat): ?>
                                <a href="category.php?slug=<?php echo $cat['slug']; ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $cat['id'] == $category['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                    <span class="badge bg-<?php echo $cat['id'] == $category['id'] ? 'light' : 'primary'; ?> rounded-pill">
                                        <?php echo $cat['articles_count']; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Категории не найдены</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>