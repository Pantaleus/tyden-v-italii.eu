<section class="hero">
    <div class="container">
        <h1>Zápisky a články ✍️</h1>
        <p>Inspirace, rady na cesty, fotogalerie a podrobné zážitky z mých toulek po Itálii.</p>
    </div>
</section>

<div class="container">
    <?php if (empty($posts)): ?>
        <p class="text-center" style="font-size: 18px; margin: 40px 0;"><?= $t('blog.no_posts') ?></p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($posts as $post): ?>
                <article class="card">
                    <?php if ($post['cover_image']): ?>
                        <div class="card-image" style="background-image: url('<?= htmlspecialchars($post['cover_image']) ?>')"></div>
                    <?php endif; ?>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <p><?= htmlspecialchars($post['meta_description']) ?></p>
                        <div class="card-footer">
                            <span class="card-date">
                                <i class="fa-regular fa-calendar"></i> 
                                <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                            </span>
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn"><?= $t('trip.read_more') ?></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
