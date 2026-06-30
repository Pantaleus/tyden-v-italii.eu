<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card-table" style="max-width: 900px; margin: 0 auto;">
    <form action="" method="POST">
        <!-- General Fields -->
        <h3 style="font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-circle-info"></i> Základní informace o cestě
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="start_date">Termín od *</label>
                <input type="date" name="start_date" id="start_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_date">Termín do *</label>
                <input type="date" name="end_date" id="end_date" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label for="cover_image">URL úvodního obrázku</label>
            <input type="text" name="cover_image" id="cover_image" class="form-control" placeholder="https://example.com/images/rome.jpg">
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 30px;">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked style="width: 18px; height: 18px;">
            <label for="is_active" style="margin-bottom: 0;">Zobrazit cestu veřejně (Aktivní)</label>
        </div>

        <!-- Localization Tabs -->
        <h3 style="font-size: 18px; margin-bottom: 12px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-language"></i> Překlady obsahu cesty
        </h3>

        <div class="lang-tabs">
            <button type="button" class="tab-btn active" onclick="switchTab(event, 'lang-cs')">Čeština (CZ)</button>
            <button type="button" class="tab-btn" onclick="switchTab(event, 'lang-en')">English (EN)</button>
            <button type="button" class="tab-btn" onclick="switchTab(event, 'lang-it')">Italiano (IT)</button>
        </div>

        <!-- CZ Tab -->
        <div id="lang-cs" class="tab-pane active">
            <div class="form-group">
                <label for="title_cs">Název cesty (CZ) *</label>
                <input type="text" name="title_cs" id="title_cs" class="form-control" placeholder="Např. Nádherný týden v Římě" required>
            </div>
            <div class="form-group">
                <label for="description_cs">Popis cesty (CZ) *</label>
                <textarea name="description_cs" id="description_cs" rows="5" class="form-control" placeholder="Shrnutí zážitků a trasy..."></textarea>
            </div>
        </div>

        <!-- EN Tab -->
        <div id="lang-en" class="tab-pane">
            <div class="form-group">
                <label for="title_en">Název cesty (EN)</label>
                <input type="text" name="title_en" id="title_en" class="form-control" placeholder="E.g. A Beautiful Week in Rome">
            </div>
            <div class="form-group">
                <label for="description_en">Popis cesty (EN)</label>
                <textarea name="description_en" id="description_en" rows="5" class="form-control" placeholder="Summary of experiences..."></textarea>
            </div>
        </div>

        <!-- IT Tab -->
        <div id="lang-it" class="tab-pane">
            <div class="form-group">
                <label for="title_it">Název cesty (IT)</label>
                <input type="text" name="title_it" id="title_it" class="form-control" placeholder="Es. Una bella settimana a Roma">
            </div>
            <div class="form-group">
                <label for="description_it">Popis cesty (IT)</label>
                <textarea name="description_it" id="description_it" rows="5" class="form-control" placeholder="Sommario del viaggio..."></textarea>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 16px;">
            <button type="submit" class="btn" style="flex-grow: 1; justify-content: center; padding: 14px;">
                <i class="fa-regular fa-circle-check"></i> Vytvořit cestu a pokračovat na Časovou Osu
            </button>
            <a href="/admin/trips" class="btn btn-secondary" style="padding: 14px;">Zrušit</a>
        </div>
    </form>
</div>

<!-- Tabs Switcher JS -->
<script>
    function switchTab(evt, tabId) {
        // Hide all tab panes
        const panes = document.querySelectorAll('.tab-pane');
        panes.forEach(pane => pane.classList.remove('active'));

        // Remove active class from buttons
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        // Show selected tab pane & set active button
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }
</script>
