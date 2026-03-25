<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var array $currentRole
 */
?>
<div style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="background: #e8f5e9; border: 2px solid #4caf50; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h2 style="color: #2e7d32; margin-top: 0;">Access Granted</h2>
        <p>You have access to <strong>Statistics</strong> as <strong><?= h($currentRole['name']) ?></strong> (Role ID: <?= $currentRole['id'] ?>)</p>
    </div>

    <h1><?= h($pageTitle) ?></h1>
    <p>User statistics page. Requires any authenticated user.</p>

    <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><a href="/dashboard">Dashboard</a></li>
        </ul>
    </div>
</div>
