<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var array $currentRole
 */
?>
<div style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="background: #fff3e0; border: 2px solid #ff9800; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h2 style="color: #e65100; margin-top: 0;">Access Granted (Moderator+)</h2>
        <p>You have access to <strong>Audit Log</strong> as <strong><?= h($currentRole['name']) ?></strong> (Role ID: <?= $currentRole['id'] ?>)</p>
    </div>

    <h1><?= h($pageTitle) ?></h1>
    <p>System audit log. Requires moderator or higher role.</p>

    <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <ul>
            <li><a href="/reports">Back to Reports</a></li>
            <li><a href="/">Back to Homepage</a></li>
        </ul>
    </div>
</div>
