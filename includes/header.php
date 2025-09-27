<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Новостной сайт'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Quill CSS -->
    <link href="../assets/css/vendors/quill.snow.css" rel="stylesheet">
    <style>
        .ql-editor { 
            min-height: 200px; 
            font-size: 16px;
        }
        
        /* Стили для кнопки переключения HTML */
        .html-toggle-btn {
            margin-right: 10px;
            padding: 5px 10px;
            border: 1px solid #ccc;
            background: white;
            border-radius: 3px;
            cursor: pointer;
        }
        .html-toggle-btn.active {
            background: #007bff;
            color: white;
        }
        
        /* Стили для HTML редактора */
        .html-editor {
            width: 100%;
            height: 400px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }
        
        .article-content img { 
            max-width: 100%; 
            height: auto; 
        }
        #editor {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Новостной сайт</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../index.php">Главная</a>
                <a class="nav-link" href="index.php">Статьи</a>
                <a class="nav-link" href="categories.php">Категории</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">