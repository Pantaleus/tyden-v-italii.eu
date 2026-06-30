<section class="hero">
    <div class="container">
        <h1>Moje cesty po Itálii 🗺️</h1>
        <p>Prohlédněte si přehled mých cest se strukturovanými itineráři a časovými osami přesunů.</p>
    </div>
</section>

<div class="container">
    <?php if (empty($trips)): ?>
        <p class="text-center" style="font-size: 18px; margin: 40px 0;"><?= $t('trip.no_trips') ?></p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($trips as $trip): ?>
                <article class="card">
                    <div class="card-image" style="background-image: url('<?= $trip['cover_image'] ?: '/public/images/trip-placeholder.jpg' ?>')"></div>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($trip['title']) ?></h3>
                        <p><?= htmlspecialchars($trip['description']) ?></p>
                        <div class="card-footer">
                            <span class="card-date">
                                <i class="fa-regular fa-calendar-days"></i> 
                                <?= date('d.m.Y', strtotime($trip['start_date'])) ?> - <?= date('d.m.Y', strtotime($trip['end_date'])) ?>
                            </span>
                            <a href="/trips/<?= $trip['id'] ?>" class="btn"><?= $t('trip.details') ?></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
