<?php if ($alert): ?>
    <div class="alert alert-<?= $alert['type'] ?>">
        <?= htmlspecialchars($alert['message']) ?>
    </div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; align-items: start;">
    
    <!-- LEFT PANEL: Trip Details & Translations -->
    <div class="card-table">
        <form action="" method="POST">
            <input type="hidden" name="action" value="save_trip">
            
            <h3 style="font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
                <i class="fa-solid fa-circle-info"></i> Upravit informace o cestě
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="start_date">Termín od *</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($trip['start_date']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Termín do *</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($trip['end_date']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="cover_image">URL úvodního obrázku</label>
                <input type="text" name="cover_image" id="cover_image" class="form-control" value="<?= htmlspecialchars($trip['cover_image'] ?? '') ?>">
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 30px;">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?= $trip['is_active'] ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                <label for="is_active" style="margin-bottom: 0;">Zobrazit cestu veřejně (Aktivní)</label>
            </div>

            <!-- Translation Tabs -->
            <h3 style="font-size: 18px; margin-bottom: 12px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
                <i class="fa-solid fa-language"></i> Překlady obsahu
            </h3>

            <div class="lang-tabs">
                <button type="button" class="tab-btn active" onclick="switchLangTab(event, 'lang-cs')">Čeština (CZ)</button>
                <button type="button" class="tab-btn" onclick="switchLangTab(event, 'lang-en')">English (EN)</button>
                <button type="button" class="tab-btn" onclick="switchLangTab(event, 'lang-it')">Italiano (IT)</button>
            </div>

            <!-- CZ Tab -->
            <div id="lang-cs" class="tab-pane active">
                <div class="form-group">
                    <label for="title_cs">Název cesty (CZ) *</label>
                    <input type="text" name="title_cs" id="title_cs" class="form-control" value="<?= htmlspecialchars($translations['cs']['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="description_cs">Popis cesty (CZ) *</label>
                    <textarea name="description_cs" id="description_cs" rows="5" class="form-control"><?= htmlspecialchars($translations['cs']['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- EN Tab -->
            <div id="lang-en" class="tab-pane">
                <div class="form-group">
                    <label for="title_en">Název cesty (EN)</label>
                    <input type="text" name="title_en" id="title_en" class="form-control" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="description_en">Popis cesty (EN)</label>
                    <textarea name="description_en" id="description_en" rows="5" class="form-control"><?= htmlspecialchars($translations['en']['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- IT Tab -->
            <div id="lang-it" class="tab-pane">
                <div class="form-group">
                    <label for="title_it">Název cesty (IT)</label>
                    <input type="text" name="title_it" id="title_it" class="form-control" value="<?= htmlspecialchars($translations['it']['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="description_it">Popis cesty (IT)</label>
                    <textarea name="description_it" id="description_it" rows="5" class="form-control"><?= htmlspecialchars($translations['it']['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 16px;">
                <button type="submit" class="btn" style="flex-grow: 1; justify-content: center;">
                    <i class="fa-regular fa-floppy-disk"></i> Uložit základní informace
                </button>
                <a href="/admin/trips" class="btn btn-secondary">Zpět</a>
            </div>
        </form>
    </div>

    <!-- RIGHT PANEL: Timeline Steps Manager -->
    <div class="card-table">
        <h3 style="font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
            <i class="fa-solid fa-route"></i> Časová osa přesunů (Timeline)
        </h3>

        <!-- Add Step Form -->
        <form id="timeline-step-form" action="" method="POST" style="background-color: var(--bg-color); padding: 20px; border-radius: var(--border-radius); border: 1px solid var(--border-color); margin-bottom: 30px;">
            <input type="hidden" name="action" id="step_action" value="add_step">
            <input type="hidden" name="edit_step_id" id="edit_step_id" value="">
            
            <h4 id="timeline-form-title" style="font-size: 15px; margin-bottom: 15px;"><i class="fa-solid fa-circle-plus"></i> Přidat nový krok časové osy</h4>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="step_type" style="font-size: 13px;">Doprava / Ikona</label>
                    <select name="step_type" id="step_type" class="form-control" style="padding: 8px;">
                        <option value="flight">Odlet / Letadlo (Plane)</option>
                        <option value="train">Vlak (Train)</option>
                        <option value="bus">Autobus (Bus)</option>
                        <option value="walk">Chůze / Pěšky (Walk)</option>
                        <option value="hotel">Ubytování / Hotel (Hotel)</option>
                        <option value="car">Autopůjčovna / Auto (Car)</option>
                        <option value="taxi">Taxi (Taxi)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="step_order" style="font-size: 13px;">Pořadí</label>
                    <input type="number" name="step_order" id="step_order" class="form-control" value="<?= count($steps) + 1 ?>" style="padding: 8px;" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="step_dep" style="font-size: 13px;">Čas Od (Departure)</label>
                    <input type="text" name="step_dep" id="step_dep" class="form-control" placeholder="Např. 10:15 nebo Den 1" style="padding: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="step_arr" style="font-size: 13px;">Čas Do (Arrival)</label>
                    <input type="text" name="step_arr" id="step_arr" class="form-control" placeholder="Např. 12:00" style="padding: 8px;">
                </div>
            </div>

            <!-- Translate Step Title and Details -->
            <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                <div class="form-group" style="margin-bottom: 8px;">
                    <input type="text" name="step_title_cs" id="step_title_cs" class="form-control" placeholder="Název kroku CZ (např. Let z Prahy)" style="padding: 8px;" required>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <textarea name="step_text_cs" id="step_text_cs" rows="2" class="form-control" placeholder="Podrobnosti CZ (např. Ryanair, letadlo letělo včas)" style="padding: 8px; font-size: 14px;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 8px;">
                    <input type="text" name="step_title_en" id="step_title_en" class="form-control" placeholder="Název kroku EN" style="padding: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <textarea name="step_text_en" id="step_text_en" rows="2" class="form-control" placeholder="Podrobnosti EN" style="padding: 8px; font-size: 14px;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 8px;">
                    <input type="text" name="step_title_it" id="step_title_it" class="form-control" placeholder="Název kroku IT" style="padding: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <textarea name="step_text_it" id="step_text_it" rows="2" class="form-control" placeholder="Podrobnosti IT" style="padding: 8px; font-size: 14px;"></textarea>
                </div>
            </div>

            <button type="submit" id="timeline-submit-btn" class="btn btn-sm" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-plus"></i> Přidat krok na časovou osu
            </button>
            <button type="button" id="cancel-edit-step-btn" class="btn btn-sm" style="display: none; width: 100%; justify-content: center; margin-top: 8px; background-color: #8c7a6b; color: #FFFFFF;" onclick="cancelEditStep()">
                <i class="fa-solid fa-xmark"></i> Zrušit úpravu
            </button>
        </form>

        <!-- Current Steps List -->
        <h4 style="font-size: 15px; margin-bottom: 15px;"><i class="fa-solid fa-list-ol"></i> Aktuální kroky na ose (Chyťte a přetáhněte pro změnu pořadí)</h4>

        <?php if (empty($steps)): ?>
            <p style="color: var(--text-muted); font-style: italic;">Časová osa nemá žádné kroky.</p>
        <?php else: ?>
            <div id="timeline-steps-list">
                <?php foreach ($steps as $st): ?>
                    <div class="timeline-builder-item" draggable="true" data-id="<?= $st['id'] ?>" data-transport="<?= htmlspecialchars($st['transport_type']) ?>" style="cursor: grab; user-select: none;">
                        <div style="float: right; display: flex; gap: 8px;">
                            <!-- Edit Button -->
                            <?php $stepJson = htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="edit-step-btn" title="Upravit" data-step="<?= $stepJson ?>" onclick="startEditStep(this)" style="background: none; border: none; color: var(--primary-color); cursor: pointer; padding: 4px;"><i class="fa-regular fa-pen-to-square" style="font-size: 16px;"></i></button>

                            <!-- Delete Step Button -->
                            <form action="" method="POST" style="display: inline; margin: 0; padding: 0;" onsubmit="return confirm('Smazat tento krok?')">
                                <input type="hidden" name="action" value="delete_step">
                                <input type="hidden" name="step_id" value="<?= $st['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #EF4444; cursor: pointer; padding: 4px;" title="Smazat"><i class="fa-regular fa-trash-can" style="font-size: 16px;"></i></button>
                            </form>
                        </div>

                        <div class="step-order-number" style="font-weight: 700; font-size: 14px; color: var(--primary-color); display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-grip-vertical" style="color: var(--text-muted); cursor: grab; font-size: 16px;"></i>
                            <span>Krok #<?= htmlspecialchars((string)$st['step_order']) ?> - <?= htmlspecialchars($st['transport_type']) ?></span>
                        </div>
                        <div style="font-size: 14px; font-weight: 600; margin-top: 4px; padding-left: 24px;">
                            <?= htmlspecialchars($st['trans']['cs']['title'] ?? 'Bez názvu') ?> 
                            <?php if ($st['departure_time'] || $st['arrival_time']): ?>
                                <span style="color: var(--text-muted); font-size: 13px;">
                                    (<?= htmlspecialchars((string)$st['departure_time']) ?> &rarr; <?= htmlspecialchars((string)$st['arrival_time']) ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($st['trans']['cs']['text']) && $st['trans']['cs']['text']): ?>
                            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px; padding-left: 24px;">
                                <?= htmlspecialchars($st['trans']['cs']['text']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .timeline-builder-item.dragging {
        opacity: 0.4;
        border: 2px dashed var(--primary-color);
        background-color: var(--bg-color);
    }
</style>

<script>
    function switchLangTab(evt, tabId) {
        const panes = document.querySelectorAll('.tab-pane');
        panes.forEach(pane => pane.classList.remove('active'));

        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }

    function startEditStep(btn) {
        const step = JSON.parse(btn.getAttribute('data-step'));
        
        document.getElementById('step_action').value = 'edit_step';
        document.getElementById('edit_step_id').value = step.id;
        document.getElementById('step_type').value = step.transport_type;
        document.getElementById('step_order').value = step.step_order;
        document.getElementById('step_dep').value = step.departure_time || '';
        document.getElementById('step_arr').value = step.arrival_time || '';
        
        // Fill translations
        document.getElementById('step_title_cs').value = step.trans?.cs?.title || '';
        document.getElementById('step_text_cs').value = step.trans?.cs?.text || '';

        document.getElementById('step_title_en').value = step.trans?.en?.title || '';
        document.getElementById('step_text_en').value = step.trans?.en?.text || '';

        document.getElementById('step_title_it').value = step.trans?.it?.title || '';
        document.getElementById('step_text_it').value = step.trans?.it?.text || '';

        // UI Updates
        document.getElementById('timeline-form-title').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Upravit krok časové osy';
        document.getElementById('timeline-submit-btn').innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Uložit změny kroku';
        document.getElementById('cancel-edit-step-btn').style.display = 'flex';

        // Scroll to form
        document.getElementById('timeline-step-form').scrollIntoView({ behavior: 'smooth' });
    }

    function cancelEditStep() {
        document.getElementById('timeline-step-form').reset();
        document.getElementById('step_action').value = 'add_step';
        document.getElementById('edit_step_id').value = '';
        
        document.getElementById('timeline-form-title').innerHTML = '<i class="fa-solid fa-circle-plus"></i> Přidat nový krok časové osy';
        document.getElementById('timeline-submit-btn').innerHTML = '<i class="fa-solid fa-plus"></i> Přidat krok na časovou osu';
        document.getElementById('cancel-edit-step-btn').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const list = document.getElementById('timeline-steps-list');
        if (!list) return;

        let draggedItem = null;

        list.querySelectorAll('.timeline-builder-item').forEach(item => {
            item.addEventListener('dragstart', (e) => {
                draggedItem = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                draggedItem = null;
                saveNewOrder();
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                const bounding = item.getBoundingClientRect();
                const offset = bounding.y + (bounding.height / 2);
                if (e.clientY - offset < 0) {
                    list.insertBefore(draggedItem, item);
                } else {
                    list.insertBefore(draggedItem, item.nextSibling);
                }
            });
        });

        async function saveNewOrder() {
            const items = list.querySelectorAll('.timeline-builder-item');
            const order = Array.from(items).map(item => item.getAttribute('data-id'));

            // Update numbering visually
            items.forEach((item, index) => {
                const stepNumText = item.querySelector('.step-order-number span');
                if (stepNumText) {
                    const transportType = item.getAttribute('data-transport');
                    stepNumText.textContent = `Krok #${index + 1} - ${transportType}`;
                }
            });

            try {
                const response = await fetch('/admin/trips/timeline/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showToast('Pořadí kroků na časové ose bylo uloženo.');
                } else {
                    alert('Chyba při ukládání pořadí: ' + (result.message || 'Neznámá chyba'));
                }
            } catch (e) {
                console.error(e);
                alert('Nepodařilo se uložit pořadí (zkontrolujte připojení).');
            }
        }

        function showToast(message) {
            let toast = document.getElementById('drag-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'drag-toast';
                toast.style.position = 'fixed';
                toast.style.bottom = '20px';
                toast.style.right = '20px';
                toast.style.backgroundColor = '#10B981';
                toast.style.color = '#FFFFFF';
                toast.style.padding = '12px 24px';
                toast.style.borderRadius = '8px';
                toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                toast.style.fontSize = '14px';
                toast.style.fontWeight = 'bold';
                toast.style.zIndex = '9999';
                toast.style.transition = 'opacity 0.3s ease';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.style.opacity = '1';
            setTimeout(() => {
                toast.style.opacity = '0';
            }, 2000);
        }
    });
</script>
