<div class="card-table">
    <div class="table-header-row">
        <h3>Seznam článků (Blog)</h3>
        <a href="/admin/posts/add" class="btn"><i class="fa-solid fa-plus"></i> Napsat nový článek</a>
    </div>

    <?php if (empty($posts)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">Zatím jste nenapsali žádný článek.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Název článku (CS)</th>
                    <th>Datum vytvoření</th>
                    <th>Stav</th>
                    <th style="text-align: right;">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($post['title'] ?? 'Bez názvu') ?></strong>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                        <td>
                            <?php if ($post['is_active']): ?>
                                <span class="badge badge-approved">Aktivní</span>
                            <?php else: ?>
                                <span class="badge badge-spam">Neaktivní</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div class="actions-cell" style="justify-content: flex-end;">
                                <a href="/admin/posts/edit/<?= $post['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-regular fa-pen-to-square"></i> Upravit</a>
                                <a href="/admin/posts/delete/<?= $post['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete tento článek smazat?')"><i class="fa-regular fa-trash-can"></i> Smazat</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
