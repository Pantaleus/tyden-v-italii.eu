<section class="hero">
    <div class="container">
        <h1>Napište mi ✉️</h1>
        <p>Máte nějaký dotaz, tip na zajímavé místo v Itálii, nebo chcete jen pozdravit? Neváhejte mě kontaktovat!</p>
    </div>
</section>

<div class="container" style="max-width: 600px; margin: 0 auto 60px auto;">
    <?php if ($alert): ?>
        <div class="alert alert-<?= $alert['type'] === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($alert['message']) ?>
        </div>
    <?php endif; ?>

    <div style="background-color: var(--card-bg); padding: 40px; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--box-shadow);">
        <form action="" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- Honeypot Field -->
            <div class="form-group hp-field">
                <label for="phone_number">Telefonní číslo (ponechte prázdné)</label>
                <input type="text" id="phone_number" name="phone_number" class="form-control" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="name">Vaše jméno *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Váš e-mail *</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="message">Zpráva *</label>
                <textarea id="message" name="message" class="form-control" rows="6" required></textarea>
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 14px;">Odeslat zprávu</button>
        </form>
    </div>
</div>
