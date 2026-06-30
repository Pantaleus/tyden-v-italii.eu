<?php
// Icon mapping helper
$getIconClass = function(string $type): string {
    return match (strtolower($type)) {
        'flight', 'plane' => 'fa-solid fa-plane',
        'train' => 'fa-solid fa-train',
        'bus' => 'fa-solid fa-bus',
        'walk', 'foot' => 'fa-solid fa-person-walking',
        'hotel', 'accommodation' => 'fa-solid fa-hotel',
        'car', 'rental_car' => 'fa-solid fa-car',
        'taxi' => 'fa-solid fa-taxi',
        default => 'fa-solid fa-location-dot',
    };
};
?>

<div class="container">
    <div style="margin-bottom: 24px; margin-top: 20px;">
        <a href="/trips" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Zpět na cesty</a>
    </div>

    <article class="trip-detail-header">
        <h1 style="font-size: 42px; margin-bottom: 12px;"><?= htmlspecialchars($trip['title']) ?></h1>
        <div style="font-size: 15px; color: var(--text-muted); margin-bottom: 24px;">
            <i class="fa-regular fa-calendar-days"></i> 
            <strong>Termín:</strong> 
            <?= date('d.m.Y', strtotime($trip['start_date'])) ?> - <?= date('d.m.Y', strtotime($trip['end_date'])) ?>
        </div>
        
        <?php if ($trip['cover_image']): ?>
            <img src="<?= htmlspecialchars($trip['cover_image']) ?>" alt="<?= htmlspecialchars($trip['title']) ?>" class="post-cover">
        <?php endif; ?>

        <div style="font-size: 18px; line-height: 1.8; margin-bottom: 40px; color: var(--text-color);">
            <?= nl2br(htmlspecialchars($trip['description'])) ?>
        </div>
    </article>

    <h2 style="font-size: 32px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-bottom: 30px;">
        <i class="fa-solid fa-route"></i> <?= $t('trip.timeline') ?>
    </h2>

    <?php if (empty($steps)): ?>
        <p>Pro tuto cestu zatím není zaznamenána časová osa.</p>
    <?php else: ?>
        <div class="timeline-container">
            <?php foreach ($steps as $index => $step): ?>
                <div class="timeline-step" style="animation-delay: <?= $index * 0.1 ?>s;">
                    <div class="timeline-icon-wrap" title="<?= htmlspecialchars($step['transport_type']) ?>">
                        <i class="<?= $getIconClass($step['transport_type']) ?>"></i>
                    </div>
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <h4><?= htmlspecialchars($step['title']) ?></h4>
                            <?php if ($step['departure_time'] || $step['arrival_time']): ?>
                                <span class="timeline-time">
                                    <i class="fa-regular fa-clock"></i> 
                                    <?= htmlspecialchars($step['departure_time']) ?>
                                    <?php if ($step['departure_time'] && $step['arrival_time']): ?> &rarr; <?php endif; ?>
                                    <?= htmlspecialchars($step['arrival_time']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($step['text']): ?>
                            <p style="margin: 0; font-size: 15px;"><?= nl2br(htmlspecialchars($step['text'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
