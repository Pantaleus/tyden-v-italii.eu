<?php if ($alert): ?>
    <div class="alert alert-<?= $alert['type'] ?>">
        <?= htmlspecialchars($alert['message']) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; align-items: start;">
    
    <!-- Left panel: List admins -->
    <div class="card-table">
        <h3>Seznam administrátorů</h3>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">
            Seznam uživatelských účtů, které mají přístup do tohoto administračního rozhraní.
        </p>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Vytvořen</th>
                    <th style="text-align: right;">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adminsList as $admin): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($admin['email']) ?></strong>
                            <?php if ($admin['id'] === $_SESSION['admin_id']): ?>
                                <span style="font-size: 11px; background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: 600; margin-left: 8px;">Můj účet</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($admin['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <?php if ($admin['id'] !== $_SESSION['admin_id']): ?>
                                <a href="?delete_id=<?= $admin['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete tohoto administrátora smazat?')"><i class="fa-regular fa-trash-can"></i> Smazat</a>
                            <?php else: ?>
                                <?php 
                                $qrData = json_encode([
                                    'url'   => BASE_URL,
                                    'token' => $admin['qr_login_token']
                                ]);
                                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
                                ?>
                                <button type="button" class="btn btn-sm" onclick="showQrModal('<?= $qrUrl ?>')" style="background-color: #0F5132; color: #FFFFFF; margin-right: 8px;">
                                    <i class="fa-solid fa-qrcode"></i> Zobrazit QR
                                </button>
                                <form action="" method="POST" style="display: inline; margin: 0; padding: 0;">
                                    <input type="hidden" name="action" value="regenerate_qr">
                                    <button type="submit" class="btn btn-sm" onclick="return confirm('Pozor, po přegenerování se budete muset v mobilní aplikaci přihlásit znovu. Pokračovat?')" style="background-color: #D97706; color: #FFFFFF; margin-right: 8px;">
                                        <i class="fa-solid fa-rotate"></i> Nový QR
                                    </button>
                                </form>
                                <span style="font-size: 13px; color: var(--text-muted); font-style: italic;">Nelze smazat</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Right panel: Create admin -->
    <div class="card-table">
        <h3>Vytvořit nového administrátora</h3>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">
            Přidejte nový email a silné heslo pro dalšího správce webu.
        </p>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-mailová adresa *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="partner@tyden-v-italii.eu" required>
            </div>
            
            <div class="form-group">
                <label for="password">Heslo *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Minimálně 8 znaků" required>
            </div>

            <button type="submit" class="btn" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
                <i class="fa-solid fa-user-plus"></i> Vytvořit účet správce
            </button>
        </form>
    </div>
</div>

<!-- Modal structure -->
<div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background-color: #FFFFFF; border: 1px solid var(--border-color); padding: 30px; border-radius: var(--border-radius); max-width: 400px; width: 100%; text-align: center; box-shadow: var(--box-shadow); position: relative; color: #112233;">
        <button onclick="closeQrModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        <h3 style="font-size: 18px; margin-bottom: 15px;"><i class="fa-solid fa-mobile-screen-button"></i> Můj přihlašovací QR kód</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.4;">Tento kód nikomu neukazujte ani neposílejte. Slouží výhradně pro přihlášení vašeho účtu do mobilní aplikace.</p>
        <div id="qrCodeContainer" style="margin-bottom: 10px;">
            <!-- QR code image will be set here -->
        </div>
    </div>
</div>

<script>
function showQrModal(url) {
    const modal = document.getElementById('qrModal');
    const container = document.getElementById('qrCodeContainer');
    container.innerHTML = `<img src="${url}" alt="QR Code" style="border: 4px solid #FFFFFF; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 0 auto; display: block;">`;
    modal.style.display = 'flex';
}
function closeQrModal() {
    document.getElementById('qrModal').style.display = 'none';
}
// Close on outside click
window.onclick = function(event) {
    const modal = document.getElementById('qrModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>
