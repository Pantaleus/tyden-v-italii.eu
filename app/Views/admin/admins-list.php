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
