<div class="card-table">
    <div class="table-header-row">
        <h3>Seznam zaznamenaných cest</h3>
        <a href="/admin/trips/add" class="btn"><i class="fa-solid fa-plus"></i> Přidat cestu</a>
    </div>

    <?php if (empty($trips)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">Zatím jste nepřidali žádnou cestu.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Název cesty (CS)</th>
                    <th>Termín od</th>
                    <th>Termín do</th>
                    <th>Stav</th>
                    <th style="text-align: right;">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($trip['title'] ?? 'Bez názvu') ?></strong>
                        </td>
                        <td><?= date('d.m.Y', strtotime($trip['start_date'])) ?></td>
                        <td><?= date('d.m.Y', strtotime($trip['end_date'])) ?></td>
                        <td>
                            <?php if ($trip['is_active']): ?>
                                <span class="badge badge-approved">Aktivní</span>
                            <?php else: ?>
                                <span class="badge badge-spam">Neaktivní</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div class="actions-cell" style="justify-content: flex-end;">
                                <a href="/admin/trips/edit/<?= $trip['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-regular fa-pen-to-square"></i> Upravit</a>
                                <a href="/admin/trips/delete/<?= $trip['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete tuto cestu smazat?')"><i class="fa-regular fa-trash-can"></i> Smazat</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
