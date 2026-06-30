<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Administrace') ?> | Týden v Itálii</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin stylesheet -->
    <link rel="stylesheet" href="/public/css/admin.css">

    <!-- TinyMCE Rich Text Editor -->
    <?php $tinyKey = \App\Core\Settings::get('tinymce_api_key', 'no-api-key'); ?>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea.rich-editor',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            height: 400
        });
    </script>
</head>
<body>

    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-plane-departure"></i> Týden v Itálii
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="/admin/dashboard" class="<?= $viewPath === 'admin/dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="/admin/trips" class="<?= str_starts_with($viewPath, 'admin/trip') ? 'active' : '' ?>"><i class="fa-solid fa-route"></i> Správa cest</a></li>
                    <li><a href="/admin/posts" class="<?= str_starts_with($viewPath, 'admin/post') ? 'active' : '' ?>"><i class="fa-solid fa-file-pen"></i> Správa článků</a></li>
                    <li><a href="/admin/comments" class="<?= str_starts_with($viewPath, 'admin/comment') ? 'active' : '' ?>"><i class="fa-solid fa-comments"></i> Komentáře</a></li>
                    <li><a href="/admin/admins" class="<?= str_starts_with($viewPath, 'admin/admin-list') ? 'active' : '' ?>"><i class="fa-solid fa-users-gear"></i> Administrátoři</a></li>
                    <li><a href="/admin/settings" class="<?= $viewPath === 'admin/settings' ? 'active' : '' ?>"><i class="fa-solid fa-gears"></i> Nastavení webu</a></li>
                    <li class="logout-item"><a href="/admin/logout"><i class="fa-solid fa-right-from-bracket"></i> Odhlásit se</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Admin Content Area -->
        <div class="admin-main">
            <header class="admin-topbar">
                <h2><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
                <div class="user-info">
                    <span class="user-badge"><i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['admin_email'] ?? 'Administrátor') ?></span>
                    <a href="/" target="_blank" class="view-site-link"><i class="fa-solid fa-square-share-nodes"></i> Zobrazit web</a>
                </div>
            </header>

            <div class="admin-content">
                <?= $content ?>
            </div>
        </div>
    </div>

</body>
</html>
