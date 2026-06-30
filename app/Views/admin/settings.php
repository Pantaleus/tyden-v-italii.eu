<?php if ($alert): ?>
    <div class="alert alert-<?= $alert['type'] ?>">
        <?= htmlspecialchars($alert['message']) ?>
    </div>
<?php endif; ?>

<div class="card-table" style="max-width: 800px; margin: 0 auto;">
    <form action="" method="POST">
        <h3 style="font-size: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-bottom: 24px;">
            <i class="fa-solid fa-palette"></i> Vzhled a zobrazení
        </h3>
        
        <div class="form-group">
            <label for="active_theme">Aktivní grafické téma webu</label>
            <select name="active_theme" id="active_theme" class="form-control">
                <option value="warm_mediterranean" <?= ($settings['active_theme'] ?? '') === 'warm_mediterranean' ? 'selected' : '' ?>>
                    Teplé Středomoří (Warm Mediterranean - krémová, terakotová, tmavomodrá)
                </option>
                <option value="italian_tricolore" <?= ($settings['active_theme'] ?? '') === 'italian_tricolore' ? 'selected' : '' ?>>
                    Italská Trikolóra (Italian Tricolore - bílá, zelená, červená)
                </option>
            </select>
        </div>

        <h3 style="font-size: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-top: 40px; margin-bottom: 24px;">
            <i class="fa-solid fa-code"></i> Editory a API
        </h3>

        <div class="form-group">
            <label for="tinymce_api_key">TinyMCE API Klíč (pro administraci blogu)</label>
            <input type="text" name="tinymce_api_key" id="tinymce_api_key" class="form-control" 
                   value="<?= htmlspecialchars($settings['tinymce_api_key'] ?? 'no-api-key') ?>" placeholder="Zadejte váš TinyMCE Cloud API klíč">
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 6px;">
                Získáte zdarma na stránkách Tiny.cloud. Klíč je potřebný pro zprovoznění plnohodnotného textového editoru.
            </p>
        </div>

        <h3 style="font-size: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-top: 40px; margin-bottom: 24px;">
            <i class="fa-solid fa-envelope-open-text"></i> SMTP a odesílání e-mailů (Kontaktní formulář)
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="smtp_host">SMTP Hostitel</label>
                <input type="text" name="smtp_host" id="smtp_host" class="form-control" 
                       value="<?= htmlspecialchars($settings['smtp_host'] ?? 'localhost') ?>" placeholder="mail.yourdomain.com">
            </div>
            <div class="form-group">
                <label for="smtp_port">SMTP Port</label>
                <input type="text" name="smtp_port" id="smtp_port" class="form-control" 
                       value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" placeholder="587">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="smtp_user">SMTP Uživatel</label>
                <input type="text" name="smtp_user" id="smtp_user" class="form-control" 
                       value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>" placeholder="info@tyden-v-italii.eu">
            </div>
            <div class="form-group">
                <label for="smtp_pass">SMTP Heslo</label>
                <input type="password" name="smtp_pass" id="smtp_pass" class="form-control" 
                       value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>" placeholder="••••••••">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="smtp_from_email">Odesílatelský e-mail (From)</label>
                <input type="email" name="smtp_from_email" id="smtp_from_email" class="form-control" 
                       value="<?= htmlspecialchars($settings['smtp_from_email'] ?? 'info@tyden-v-italii.eu') ?>" required>
            </div>
            <div class="form-group">
                <label for="smtp_from_name">Odesílatelské jméno</label>
                <input type="text" name="smtp_from_name" id="smtp_from_name" class="form-control" 
                       value="<?= htmlspecialchars($settings['smtp_from_name'] ?? 'Týden v Itálii') ?>" required>
            </div>
        </div>

        <button type="submit" class="btn" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px;">
            <i class="fa-regular fa-floppy-disk"></i> Uložit veškeré nastavení
        </button>
    </form>
</div>
