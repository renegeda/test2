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
        
        /* Стили для выравнивания Quill */
        .ql-editor .ql-align-center {
            text-align: center;
        }
        .ql-editor .ql-align-right {
            text-align: right;
        }
        .ql-editor .ql-align-justify {
            text-align: justify;
        }
        
        .article-content img { 
            max-width: 100%; 
            height: auto; 
        }
        .article-content iframe {
            max-width: 100%;
        }
        
        /* Стили для выравнивания в статьях */
        .article-content .ql-align-center {
            text-align: center;
        }
        .article-content .ql-align-right {
            text-align: right;
        }
        .article-content .ql-align-justify {
            text-align: justify;
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
                <a class="nav-link" href="index.php">Админка</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">