<!-- Hero Welcome Banner -->
<section class="hero">
    <div class="container">
        <h1>Benvenuti in Italia! 🇮🇹</h1>
        <p>Cestovatelský deník plný příběhů, podrobných tras, časových os a radostí z mých cest po celé Itálii.</p>
    </div>
</section>

<!-- Trips Section -->
<section class="trips-section container">
    <h2 class="text-center" style="margin-bottom: 40px; font-size: 36px;"><?= $t('nav.trips') ?></h2>
    
    <?php if (empty($trips)): ?>
        <p class="text-center"><?= $t('trip.no_trips') ?></p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($trips as $trip): ?>
                <article class="card">
                    <div class="card-image" style="background-image: url('<?= $trip['cover_image'] ?: '/public/images/trip-placeholder.jpg' ?>')">
                        <span class="card-badge">🇮🇹 <?= htmlspecialchars($trip['title']) ?></span>
                    </div>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($trip['title']) ?></h3>
                        <p><?= htmlspecialchars(mb_strimwidth($trip['description'], 0, 160, '...')) ?></p>
                        <div class="card-footer">
                            <span class="card-date">
                                <i class="fa-regular fa-calendar-days"></i> 
                                <?= date('d.m.Y', strtotime($trip['start_date'])) ?> - <?= date('d.m.Y', strtotime($trip['end_date'])) ?>
                            </span>
                            <a href="/trips/<?= $trip['id'] ?>" class="btn"><?= $t('trip.read_more') ?></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-bottom: 60px;">
            <a href="/trips" class="btn btn-secondary">Všechny cesty</a>
        </div>
    <?php endif; ?>
</section>

<!-- Blog Section -->
<section class="blog-section container" style="border-top: 1px solid var(--border-color); padding-top: 60px;">
    <h2 class="text-center" style="margin-bottom: 40px; font-size: 36px;"><?= $t('blog.recent') ?></h2>

    <?php if (empty($posts)): ?>
        <p class="text-center"><?= $t('blog.no_posts') ?></p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($posts as $post): ?>
                <article class="card">
                    <div class="card-image" style="background-image: url('<?= $post['cover_image'] ?: '/public/images/blog-placeholder.jpg' ?>')"></div>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <p><?= htmlspecialchars(mb_strimwidth(strip_tags($post['content']), 0, 160, '...')) ?></p>
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
        <div class="text-center">
            <a href="/blog" class="btn btn-secondary">Přejít na blog</a>
        </div>
    <?php endif; ?>
</section>
