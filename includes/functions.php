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
    $db = new Database();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, short_description) VALUES (?, ?, ?)");
    return $stmt->execute([$title, $content, $short_description]);
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
function processAccordions($content) {
    $pattern = '/\[\[accordion\]\](.*?)\[\[\/accordion\]\]/s';
    
    return preg_replace_callback($pattern, function($matches) {
        $items = [];
        $lines = explode("\n", trim($matches[1]));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode('::', $line, 2);
            if (count($parts) === 2) {
                $items[] = [
                    'title' => trim($parts[0]),
                    'content' => trim($parts[1])
                ];
            }
        }
        
        return generateAccordionHTML($items);
    }, $content);
}

function generateAccordionHTML($items) {
    if (empty($items)) return '';
    
    $accordion_id = 'accordion-' . uniqid();
    $html = '<div class="accordion mt-3 mb-3" id="'.$accordion_id.'">';
    
    foreach ($items as $index => $item) {
        $item_id = 'collapse-' . $accordion_id . '-' . $index;
        $show_class = $index === 0 ? 'show' : '';
        $collapsed_class = $index === 0 ? '' : 'collapsed';
        
        $html .= '
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button '.$collapsed_class.'" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#'.$item_id.'">
                    '.htmlspecialchars($item['title']).'
                </button>
            </h2>
            <div id="'.$item_id.'" class="accordion-collapse collapse '.$show_class.'" 
                 data-bs-parent="#'.$accordion_id.'">
                <div class="accordion-body">
                    '.nl2br(htmlspecialchars($item['content'])).'
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