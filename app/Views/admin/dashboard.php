<!-- Metrics Overview -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-details">
            <h4>Zobrazení stránek</h4>
            <div class="metric-value"><?= number_format($totalViews) ?></div>
        </div>
        <div class="metric-icon"><i class="fa-solid fa-eye"></i></div>
    </div>
    
    <div class="metric-card success">
        <div class="metric-details">
            <h4>Unikátní návštěvy</h4>
            <div class="metric-value"><?= number_format($uniqueVisitors) ?></div>
        </div>
        <div class="metric-icon"><i class="fa-solid fa-users"></i></div>
    </div>
    
    <div class="metric-card warning">
        <div class="metric-details">
            <h4>Země návštěvníků</h4>
            <div class="metric-value"><?= number_format($totalCountries) ?></div>
        </div>
        <div class="metric-icon"><i class="fa-solid fa-globe"></i></div>
    </div>
    
    <div class="metric-card">
        <div class="metric-details">
            <h4>Čekající komentáře</h4>
            <div class="metric-value"><?= number_format($pendingComments) ?></div>
        </div>
        <div class="metric-icon">
            <a href="/admin/comments" style="color: inherit;"><i class="fa-solid fa-comments"></i></a>
        </div>
    </div>
</div>

<!-- Detailed Stats Grid -->
<div class="stats-grid">
    <!-- Top Pages -->
    <div class="stats-card">
        <h3><i class="fa-solid fa-file-lines"></i> Nejnavštěvovanější stránky</h3>
        <div class="stat-bar-list">
            <?php if (empty($topPages)): ?>
                <p style="color: var(--text-muted);">Zatím nejsou k dispozici žádná data.</p>
            <?php else: ?>
                <?php 
                $maxViews = max(array_column($topPages, 'cnt')); 
                foreach ($topPages as $page): 
                    $percent = $maxViews > 0 ? round(($page['cnt'] / $maxViews) * 100) : 0;
                ?>
                    <div class="stat-bar-item">
                        <div class="stat-bar-labels">
                            <span><?= htmlspecialchars($page['url_path']) ?></span>
                            <strong><?= number_format($page['cnt']) ?>x</strong>
                        </div>
                        <div class="stat-bar-track">
                            <div class="stat-bar-fill" style="width: <?= $percent ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Countries -->
    <div class="stats-card">
        <h3><i class="fa-solid fa-earth-americas"></i> Nejčastější země</h3>
        <div class="stat-bar-list">
            <?php if (empty($topCountries)): ?>
                <p style="color: var(--text-muted);">Zatím nejsou k dispozici žádná data.</p>
            <?php else: ?>
                <?php 
                $maxCountries = max(array_column($topCountries, 'cnt')); 
                foreach ($topCountries as $country): 
                    $percent = $maxCountries > 0 ? round(($country['cnt'] / $maxCountries) * 100) : 0;
                ?>
                    <div class="stat-bar-item">
                        <div class="stat-bar-labels">
                            <span>
                                <span style="font-size: 13px; font-weight: 700; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; margin-right: 8px;">
                                    <?= htmlspecialchars($country['country_code']) ?>
                                </span>
                                <?= htmlspecialchars($country['country_name']) ?>
                            </span>
                            <strong><?= number_format($country['cnt']) ?>x</strong>
                        </div>
                        <div class="stat-bar-track">
                            <div class="stat-bar-fill" style="width: <?= $percent ?>%; background-color: var(--success-color);"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="stats-grid">
    <!-- Devices -->
    <div class="stats-card">
        <h3><i class="fa-solid fa-display"></i> Zařízení (Device Type)</h3>
        <div class="stat-bar-list">
            <?php if (empty($devices)): ?>
                <p style="color: var(--text-muted);">Zatím nejsou k dispozici žádná data.</p>
            <?php else: ?>
                <?php 
                $totalDev = array_sum(array_column($devices, 'cnt'));
                foreach ($devices as $dev): 
                    $percent = $totalDev > 0 ? round(($dev['cnt'] / $totalDev) * 100) : 0;
                    $icon = match(strtolower($dev['device'])) {
                        'mobile' => 'fa-mobile-screen-button',
                        'tablet' => 'fa-tablet-screen-button',
                        default => 'fa-desktop'
                    };
                ?>
                    <div class="stat-bar-item">
                        <div class="stat-bar-labels">
                            <span><i class="fa-solid <?= $icon ?>" style="margin-right: 8px; color: var(--primary-color);"></i> <?= ucfirst(htmlspecialchars($dev['device'])) ?></span>
                            <strong><?= $percent ?>% (<?= number_format($dev['cnt']) ?>)</strong>
                        </div>
                        <div class="stat-bar-track">
                            <div class="stat-bar-fill" style="width: <?= $percent ?>%; background-color: var(--sidebar-active);"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Screen Sizes -->
    <div class="stats-card">
        <h3><i class="fa-solid fa-maximize"></i> Velikosti obrazovek (Screen Sizes)</h3>
        <div class="stat-bar-list">
            <?php if (empty($screenSizes)): ?>
                <p style="color: var(--text-muted);">Zatím nejsou detekovány žádné velikosti.</p>
            <?php else: ?>
                <?php 
                $maxScreen = max(array_column($screenSizes, 'cnt')); 
                foreach ($screenSizes as $sz): 
                    $percent = $maxScreen > 0 ? round(($sz['cnt'] / $maxScreen) * 100) : 0;
                ?>
                    <div class="stat-bar-item">
                        <div class="stat-bar-labels">
                            <span><?= htmlspecialchars($sz['res']) ?></span>
                            <strong><?= number_format($sz['cnt']) ?>x</strong>
                        </div>
                        <div class="stat-bar-track">
                            <div class="stat-bar-fill" style="width: <?= $percent ?>%; background-color: var(--primary-color);"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: 1fr;">
    <!-- Browsers -->
    <div class="stats-card">
        <h3><i class="fa-solid fa-window-restore"></i> Používané prohlížeče</h3>
        <div class="stat-bar-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <?php if (empty($topBrowsers)): ?>
                <p style="color: var(--text-muted);">Žádná data k zobrazení.</p>
            <?php else: ?>
                <?php foreach ($topBrowsers as $browser): ?>
                    <div style="background-color: var(--bg-color); padding: 16px; border-radius: var(--border-radius); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <i class="fa-regular fa-compass" style="color: var(--primary-color); margin-right: 8px;"></i>
                            <strong><?= htmlspecialchars($browser['browser']) ?></strong>
                        </div>
                        <span class="badge badge-approved"><?= number_format($browser['cnt']) ?> zobrazení</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
