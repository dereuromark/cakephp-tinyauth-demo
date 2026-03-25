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
        <p>You have access to the <strong>Dashboard</strong> as <strong><?= h($currentRole['name']) ?></strong> (Role ID: <?= $currentRole['id'] ?>)</p>
    </div>

    <h1><?= h($pageTitle) ?></h1>
    <p>This page is accessible to any authenticated user (user, moderator, or admin role).</p>

    <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><a href="/dashboard/stats">View Statistics</a></li>
            <li><a href="/reports">Reports</a> (requires moderator+)</li>
            <li><a href="/admin/users">Admin Users</a> (requires admin)</li>
        </ul>
    </div>
</div>
