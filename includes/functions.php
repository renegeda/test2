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

function addArticle($title, $content, $short_description = '') {
    error_log("=== addArticle CALLED ===");
    error_log("Title: " . $title);
    error_log("Content length: " . strlen($content));
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, short_description) VALUES (?, ?, ?)");
        $result = $stmt->execute([$title, $content, $short_description]);
        
        error_log("SQL execute result: " . ($result ? 'true' : 'false'));
        error_log("Last insert ID: " . $pdo->lastInsertId());
        error_log("Row count: " . $stmt->rowCount());
        
        return $result;
    } catch (Exception $e) {
        error_log("ОШИБКА БД в addArticle: " . $e->getMessage());
        return false;
    }
}

function updateArticle($id, $title, $content, $short_description = '') {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, short_description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$title, $content, $short_description, $id]);
}

function deleteArticle($id) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    return $stmt->execute([$id]);
}

// Функция обработки аккордеонов в контенте
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

// Функция для обрезки текста
function truncateText($text, $length = 150) {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}
?>