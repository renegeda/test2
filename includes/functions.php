<?php
require_once __DIR__ . '/../config/database.php';

function getAllArticles() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticleById($id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getArticleContent($article) {
    $content = processAccordions($article['content']);
    $content = processContentImages($content);
    return $content;
}

// ==================== КАТЕГОРИИ ====================

function getAllCategories() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById($id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategoryBySlug($slug) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addCategory($name, $slug, $description = '') {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $slug, $description]);
    } catch (Exception $e) {
        error_log("ОШИБКА добавления категории: " . $e->getMessage());
        return false;
    }
}

function updateCategory($id, $name, $slug, $description = '') {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $description, $id]);
    } catch (Exception $e) {
        error_log("ОШИБКА обновления категории: " . $e->getMessage());
        return false;
    }
}

function deleteCategory($id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        // Сначала обнуляем category_id у статей этой категории
        $pdo->prepare("UPDATE articles SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        
        // Затем удаляем категорию
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("ОШИБКА удаления категории: " . $e->getMessage());
        return false;
    }
}

function getArticlesCountByCategory($category_id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['count'] : 0;
}

function getArticlesByCategory($category_id, $limit = null) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $sql = "SELECT * FROM articles WHERE category_id = ? ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoriesWithArticlesCount() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("
        SELECT c.*, COUNT(a.id) as articles_count 
        FROM categories c 
        LEFT JOIN articles a ON c.id = a.category_id 
        GROUP BY c.id 
        ORDER BY c.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== СТАТЬИ С КАТЕГОРИЯМИ ====================

function getAllArticlesWithCategories() {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("
        SELECT a.*, c.name as category_name, c.slug as category_slug 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        ORDER BY a.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticleByIdWithCategory($id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function incrementArticleViews($article_id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$article_id]);
    } catch (Exception $e) {
        error_log("ОШИБКА увеличения просмотров: " . $e->getMessage());
        return false;
    }
}

function getArticleViews($article_id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT views FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['views'] : 0;
}

function addArticle($title, $content, $short_description = '', $preview_image = '', $category_id = null) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, short_description, preview_image, category_id) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$title, $content, $short_description, $preview_image, $category_id]);
        
        return $result;
    } catch (Exception $e) {
        error_log("ОШИБКА БД в addArticle: " . $e->getMessage());
        return false;
    }
}

function updateArticle($id, $title, $content, $short_description = '', $preview_image = '', $category_id = null) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, short_description = ?, preview_image = ?, category_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$title, $content, $short_description, $preview_image, $category_id, $id]);
    } catch (Exception $e) {
        error_log("ОШИБКА БД в updateArticle: " . $e->getMessage());
        return false;
    }
}

function deleteArticle($id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    return $stmt->execute([$id]);
}

function deleteMultipleArticles($ids) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id IN ($placeholders)");
        return $stmt->execute($ids);
    } catch (Exception $e) {
        error_log("ОШИБКА массового удаления: " . $e->getMessage());
        return false;
    }
}

// Функция обработки аккордеонов в контенте (оптимизированная для Quill)
function processAccordions($content) {
    // Убираем параграфы вокруг шорткодов аккордеона
    $content = preg_replace('/<p>\[\[accordion\]\]<\/p>/', '[[accordion]]', $content);
    $content = preg_replace('/<p>\[\[\/accordion\]\]<\/p>/', '[[/accordion]]', $content);
    
    $pattern = '/\[\[accordion\]\](.*?)\[\[\/accordion\]\]/s';
    
    return preg_replace_callback($pattern, function($matches) {
        $items = [];
        $accordion_content = strip_tags($matches[1]); // Убираем HTML теги
        $accordion_content = trim($accordion_content);
        
        // Разделяем элементы аккордеона по |
        $elements = explode('|', $accordion_content);
        
        foreach ($elements as $element) {
            $element = trim($element);
            if (empty($element)) continue;
            
            // Разделяем заголовок и контент по ::
            if (strpos($element, '::') !== false) {
                $parts = explode('::', $element, 2);
                if (count($parts) === 2) {
                    $items[] = [
                        'title' => trim($parts[0]),
                        'content' => trim($parts[1])
                    ];
                }
            }
        }
        
        return generateAccordionHTML($items);
    }, $content);
}

function generateAccordionHTML($items) {
    if (empty($items)) {
        return '<div class="alert alert-warning">Аккордеон не содержит элементов</div>';
    }
    
    $accordion_id = 'accordion-' . uniqid();
    $html = '<div class="accordion mt-4 mb-4" id="' . $accordion_id . '">';
    
    foreach ($items as $index => $item) {
        $item_id = 'collapse-' . $accordion_id . '-' . $index;
        $show_class = ($index === 0) ? 'show' : '';
        $collapsed_class = ($index === 0) ? '' : 'collapsed';
        
        $html .= '
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button ' . $collapsed_class . '" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#' . $item_id . '" 
                        aria-expanded="' . ($index === 0 ? 'true' : 'false') . '" 
                        aria-controls="' . $item_id . '">
                    ' . htmlspecialchars($item['title']) . '
                </button>
            </h2>
            <div id="' . $item_id . '" class="accordion-collapse collapse ' . $show_class . '" 
                 data-bs-parent="#' . $accordion_id . '">
                <div class="accordion-body">
                    ' . nl2br(htmlspecialchars($item['content'])) . '
                </div>
            </div>
        </div>';
    }
    
    $html .= '</div>';
    return $html;
}

// Функция для обработки изображений в контенте (если нужно ресайзить и т.д.)
function processContentImages($content) {
    // Можно добавить обработку изображений, например:
    // - Добавление lazy loading
    // - Ресайз на лету
    // - Добавление CSS классов
    
    // Простой пример - добавляем классы для Bootstrap
    $content = preg_replace(
        '/<img([^>]+)>/',
        '<img$1 class="img-fluid rounded mb-3">',
        $content
    );
    
    return $content;
}

// Функция для извлечения первого изображения из контента
function extractFirstImage($content) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    return $matches[1] ?? '';
}

// Функция для обрезки текста
function truncateText($text, $length = 150) {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}
?>