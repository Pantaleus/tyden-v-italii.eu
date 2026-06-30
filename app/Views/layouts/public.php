<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' | ' : '' ?>Týden v Itálii 🇮🇹</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Cestovatelský blog a deník o mých výletech a zážitcích v kouzelné Itálii. Trasy, časové osy a fotografie.') ?>">
    <meta property="og:title" content="<?= isset($title) ? htmlspecialchars($title) : 'Týden v Itálii 🇮🇹' ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'Cestovatelský blog a deník o mých výletech v Itálii.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= isset($coverImage) ? htmlspecialchars($coverImage) : '/public/images/og-default.jpg' ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Main Style (CSS Variables change based on theme class on body) -->
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="theme-<?= htmlspecialchars($activeTheme) ?>">

    <header class="main-header">
        <div class="header-container">
            <a href="/" class="logo">
                <span class="flag-stripe green"></span>
                <span class="logo-text">Týden v Itálii</span>
                <span class="flag-stripe red"></span>
            </a>
            
            <!-- Hamburger Menu Button -->
            <button class="mobile-nav-toggle" aria-label="Toggle navigation">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="/" class="<?= $viewPath === 'public/home' ? 'active' : '' ?>"><?= $t('nav.home') ?></a></li>
                    <li><a href="/trips" class="<?= str_starts_with($viewPath, 'public/trip') ? 'active' : '' ?>"><?= $t('nav.trips') ?></a></li>
                    <li><a href="/blog" class="<?= str_starts_with($viewPath, 'public/blog') ? 'active' : '' ?>"><?= $t('nav.blog') ?></a></li>
                    <li><a href="/contact" class="<?= $viewPath === 'public/contact' ? 'active' : '' ?>"><?= $t('nav.contact') ?></a></li>
                </ul>
            </nav>

            <div class="language-switcher">
                <a href="?lang=cs" class="lang-btn <?= $lang === 'cs' ? 'active' : '' ?>" title="Čeština">CZ</a>
                <a href="?lang=en" class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>" title="English">EN</a>
                <a href="?lang=it" class="lang-btn <?= $lang === 'it' ? 'active' : '' ?>" title="Italiano">IT</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?= $content ?>
    </main>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-info">
                <h3>Týden v Itálii</h3>
                <p>Cestovatelský deník plný slunce, pizzy, památek a italské atmosféry.</p>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Týden v Itálii. <?= $t('footer.rights') ?></p>
                <p class="love-italy"><?= $t('footer.love') ?></p>
            </div>
        </div>
    </footer>

    <!-- Async Resolution Tracking Script -->
    <script>
        // Track screen resolution
        document.addEventListener('DOMContentLoaded', () => {
            const width = window.screen.width;
            const height = window.screen.height;
            
            fetch('/track-screen', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ width: width, height: height })
            }).catch(err => console.error('Stat tracking error:', err));
        });

        // Mobile Nav Toggle
        const toggleBtn = document.querySelector('.mobile-nav-toggle');
        const nav = document.querySelector('.main-nav');
        if (toggleBtn && nav) {
            toggleBtn.addEventListener('click', () => {
                nav.classList.toggle('open');
                toggleBtn.classList.toggle('open');
            });
        }
    </script>
</body>
</html>
