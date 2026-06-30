<?php if ($alert): ?>
    <div class="alert alert-<?= $alert['type'] ?>">
        <?= htmlspecialchars($alert['message']) ?>
    </div>
<?php endif; ?>

<div class="card-table">
    <h3>Správa komentářů k článkům</h3>
    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">
        Komentáře od čtenářů k blogovým příspěvkům. Nové komentáře vyžadují schválení předtím, než se zobrazí na webu.
    </p>

    <?php if (empty($comments)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">Zatím nebyly napsány žádné komentáře.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Autor / Článek</th>
                    <th style="width: 45%;">Komentář</th>
                    <th>Stav</th>
                    <th>Datum</th>
                    <th style="text-align: right;">Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($comment['author_name']) ?></strong><br>
                            <span style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($comment['author_email']) ?></span><br>
                            <span style="font-size: 12px; font-weight: 700; color: var(--primary-color);">K článku: <?= htmlspecialchars($comment['post_title'] ?? '') ?></span>
                        </td>
                        <td>
                            <div style="font-size: 14px; max-height: 100px; overflow-y: auto; background-color: var(--bg-color); padding: 10px; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </div>
                            <span style="font-size: 11px; color: var(--text-muted);">IP: <?= htmlspecialchars($comment['ip_address']) ?></span>
                        </td>
                        <td>
                            <?php if ($comment['is_approved'] == 0): ?>
                                <span class="badge badge-pending">Čeká na schválení</span>
                            <?php elseif ($comment['is_approved'] == 1): ?>
                                <span class="badge badge-approved">Schválený</span>
                            <?php else: ?>
                                <span class="badge badge-spam">Spam</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <div class="actions-cell" style="justify-content: flex-end;">
                                <?php if ($comment['is_approved'] != 1): ?>
                                    <a href="?action=approve&id=<?= $comment['id'] ?>" class="btn btn-sm btn-success" title="Schválit"><i class="fa-solid fa-check"></i> Schválit</a>
                                <?php endif; ?>
                                
                                <?php if ($comment['is_approved'] != -1): ?>
                                    <a href="?action=spam&id=<?= $comment['id'] ?>" class="btn btn-sm btn-warning" title="Označit jako spam"><i class="fa-solid fa-triangle-exclamation"></i> Spam</a>
                                <?php endif; ?>

                                <a href="?action=delete&id=<?= $comment['id'] ?>" class="btn btn-sm btn-danger" title="Smazat" onclick="return confirm('Opravdu chcete tento komentář navždy smazat?')"><i class="fa-regular fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
