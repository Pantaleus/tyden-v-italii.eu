<div class="container">
    <div style="margin-bottom: 24px; margin-top: 20px;">
        <a href="/blog" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Zpět na články</a>
    </div>

    <article class="post-detail">
        <header class="post-header">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <i class="fa-regular fa-calendar"></i> <?= $t('blog.posted_on') ?>: <?= date('d.m.Y', strtotime($post['created_at'])) ?>
            </div>
        </header>

        <?php if ($post['cover_image']): ?>
            <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-cover">
        <?php endif; ?>

        <!-- Content is rendered raw as it comes from TinyMCE in admin -->
        <div class="post-body">
            <?= $post['content'] ?>
        </div>

        <!-- Comments Section -->
        <section class="comments-section">
            <h2><i class="fa-regular fa-comments"></i> <?= $t('blog.comments') ?> (<?= count($comments) ?>)</h2>

            <?php if ($alert): ?>
                <div class="alert alert-<?= $alert['type'] === 'success' ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($alert['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($comments)): ?>
                <p style="margin: 20px 0; font-style: italic; color: var(--text-muted);"><?= $t('comment.no_comments') ?></p>
            <?php else: ?>
                <ul class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                        <li class="comment-item">
                            <div class="comment-meta">
                                <span class="comment-author"><i class="fa-solid fa-user-pen"></i> <?= htmlspecialchars($comment['author_name']) ?></span>
                                <span class="comment-date"><i class="fa-regular fa-clock"></i> <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <div class="comment-content" style="color: var(--text-color);">
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Leave Comment Form -->
            <div class="comment-form-wrap" style="background-color: var(--card-bg); padding: 32px; border-radius: var(--border-radius); border: 1px solid var(--border-color); box-shadow: var(--box-shadow); margin-top: 40px;">
                <h3><?= $t('comment.title') ?></h3>
                <form action="" method="POST" style="margin-top: 20px;">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Honeypot Anti-Spam (hidden from users) -->
                    <div class="form-group hp-field">
                        <label for="website_url">Website URL (nevyplňujte)</label>
                        <input type="text" id="website_url" name="website_url" class="form-control" autocomplete="off">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="author_name"><?= $t('comment.name') ?> *</label>
                            <input type="text" id="author_name" name="author_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="author_email"><?= $t('comment.email') ?> *</label>
                            <input type="email" id="author_email" name="author_email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment_text"><?= $t('comment.content') ?> *</label>
                        <textarea id="comment_text" name="comment_text" class="form-control" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn"><?= $t('comment.submit') ?></button>
                </form>
            </div>
        </section>
    </article>
</div>
