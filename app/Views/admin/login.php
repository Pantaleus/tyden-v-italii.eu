<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení do administrace</title>
    <!-- Google Fonts & Admin style -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/admin.css">
    <style>
        body {
            background-color: var(--sidebar-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-logo">
            Týden v Itálii 🇮🇹
        </div>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-mailová adresa</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@tyden-v-italii.eu" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Heslo</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn" style="width: 100%; justify-content: center; padding: 14px; margin-top: 10px;">
                Přihlásit se
            </button>
        </form>
    </div>

</body>
</html>
