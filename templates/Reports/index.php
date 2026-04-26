<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var array $currentRole
 */
?>
<div data-style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div data-style="background: #fff3e0; border: 2px solid #ff9800; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h2 data-style="color: #e65100; margin-top: 0;">Access Granted (Moderator+)</h2>
        <p>You have access to <strong>Reports</strong> as <strong><?= h($currentRole['name']) ?></strong> (Role ID: <?= $currentRole['id'] ?>)</p>
    </div>

    <h1><?= h($pageTitle) ?></h1>
    <p>This page requires <strong>moderator</strong> or <strong>admin</strong> role.</p>

    <div data-style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Available Reports</h3>
        <ul>
            <li><a href="/reports/usage">Usage Report</a></li>
            <li><a href="/reports/audit">Audit Log</a></li>
        </ul>
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/admin/users">Admin Users</a> (requires admin)</li>
        </ul>
    </div>
</div>
