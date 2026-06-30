<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card-table" style="max-width: 950px; margin: 0 auto;">
    <form action="" method="POST">
        <!-- General Fields -->
        <h3 style="font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-circle-info"></i> Nastavení článku
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="cover_image">URL úvodního obrázku</label>
                <input type="text" name="cover_image" id="cover_image" class="form-control" placeholder="https://example.com/images/rome-blog.jpg">
            </div>
            
            <div class="form-group">
                <label for="trip_id">Přiřadit k cestě (nepovinné)</label>
                <select name="trip_id" id="trip_id" class="form-control">
                    <option value="">Nepřiřazovat k cestě</option>
                    <?php foreach ($trips as $trip): ?>
                        <option value="<?= $trip['id'] ?>"><?= htmlspecialchars($trip['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 30px;">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked style="width: 18px; height: 18px;">
            <label for="is_active" style="margin-bottom: 0;">Zobrazit článek veřejně (Aktivní)</label>
        </div>

        <!-- Translation Tabs -->
        <h3 style="font-size: 18px; margin-bottom: 12px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-language"></i> Překlady obsahu článku
        </h3>

        <div class="lang-tabs">
            <button type="button" class="tab-btn active" onclick="switchPostTab(event, 'post-cs')">Čeština (CZ)</button>
            <button type="button" class="tab-btn" onclick="switchPostTab(event, 'post-en')">English (EN)</button>
            <button type="button" class="tab-btn" onclick="switchPostTab(event, 'post-it')">Italiano (IT)</button>
        </div>

        <!-- CZ Tab -->
        <div id="post-cs" class="tab-pane active">
            <div class="form-group">
                <label for="title_cs">Název článku (CZ) *</label>
                <input type="text" name="title_cs" id="title_cs" class="form-control" placeholder="Např. Jak přežít římské metro" required>
            </div>
            <div class="form-group">
                <label for="content_cs">Obsah článku (CZ)</label>
                <textarea name="content_cs" id="content_cs" class="form-control rich-editor"></textarea>
            </div>
            
            <h4 style="font-size: 15px; margin: 20px 0 10px 0; border-bottom: 1px solid var(--border-color); padding-bottom: 6px; color: var(--primary-color);">SEO Nastavení (CZ)</h4>
            <div class="form-group">
                <label for="meta_title_cs">Meta Title (CZ)</label>
                <input type="text" name="meta_title_cs" id="meta_title_cs" class="form-control">
            </div>
            <div class="form-group">
                <label for="meta_desc_cs">Meta Description (CZ)</label>
                <textarea name="meta_desc_cs" id="meta_desc_cs" rows="3" class="form-control"></textarea>
            </div>
        </div>

        <!-- EN Tab -->
        <div id="post-en" class="tab-pane">
            <div class="form-group">
                <label for="title_en">Název článku (EN)</label>
                <input type="text" name="title_en" id="title_en" class="form-control" placeholder="E.g. How to survive Rome metro">
            </div>
            <div class="form-group">
                <label for="content_en">Obsah článku (EN)</label>
                <textarea name="content_en" id="content_en" class="form-control rich-editor"></textarea>
            </div>

            <h4 style="font-size: 15px; margin: 20px 0 10px 0; border-bottom: 1px solid var(--border-color); padding-bottom: 6px; color: var(--primary-color);">SEO Nastavení (EN)</h4>
            <div class="form-group">
                <label for="meta_title_en">Meta Title (EN)</label>
                <input type="text" name="meta_title_en" id="meta_title_en" class="form-control">
            </div>
            <div class="form-group">
                <label for="meta_desc_en">Meta Description (EN)</label>
                <textarea name="meta_desc_en" id="meta_desc_en" rows="3" class="form-control"></textarea>
            </div>
        </div>

        <!-- IT Tab -->
        <div id="post-it" class="tab-pane">
            <div class="form-group">
                <label for="title_it">Název článku (IT)</label>
                <input type="text" name="title_it" id="title_it" class="form-control" placeholder="Es. Come sopravvivere alla metropolitana di Roma">
            </div>
            <div class="form-group">
                <label for="content_it">Obsah článku (IT)</label>
                <textarea name="content_it" id="content_it" class="form-control rich-editor"></textarea>
            </div>

            <h4 style="font-size: 15px; margin: 20px 0 10px 0; border-bottom: 1px solid var(--border-color); padding-bottom: 6px; color: var(--primary-color);">SEO Nastavení (IT)</h4>
            <div class="form-group">
                <label for="meta_title_it">Meta Title (IT)</label>
                <input type="text" name="meta_title_it" id="meta_title_it" class="form-control">
            </div>
            <div class="form-group">
                <label for="meta_desc_it">Meta Description (IT)</label>
                <textarea name="meta_desc_it" id="meta_desc_it" rows="3" class="form-control"></textarea>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 16px;">
            <button type="submit" class="btn" style="flex-grow: 1; justify-content: center; padding: 14px;">
                <i class="fa-regular fa-paper-plane"></i> Publikovat článek
            </button>
            <a href="/admin/posts" class="btn btn-secondary" style="padding: 14px;">Zrušit</a>
        </div>
    </form>
</div>

<script>
    function switchPostTab(evt, tabId) {
        const panes = document.querySelectorAll('.tab-pane');
        panes.forEach(pane => pane.classList.remove('active'));

        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }
</script>
