<?php
/**
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var array $currentRole
 * @var array<\App\Model\Entity\User>|\Cake\Collection\CollectionInterface<\App\Model\Entity\User> $users
 */
?>
<div data-style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div data-style="background: #ffebee; border: 2px solid #f44336; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h2 data-style="color: #c62828; margin-top: 0;">Access Granted (Admin Only)</h2>
        <p>You have access to <strong>Admin: Users</strong> as <strong><?= h($currentRole['name']) ?></strong> (Role ID: <?= $currentRole['id'] ?>)</p>
    </div>

    <h1><?= h($pageTitle) ?></h1>
    <p>This page requires <strong>admin</strong> role only.</p>

    <?php if (!empty($users)) { ?>
    <table data-style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
        <thead>
            <tr data-style="background: #f5f5f5;">
                <th data-style="padding: 0.5rem; border: 1px solid #ddd; text-align: left;">ID</th>
                <th data-style="padding: 0.5rem; border: 1px solid #ddd; text-align: left;">Username</th>
                <th data-style="padding: 0.5rem; border: 1px solid #ddd; text-align: left;">Email</th>
                <th data-style="padding: 0.5rem; border: 1px solid #ddd; text-align: left;">Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) { ?>
            <tr>
                <td data-style="padding: 0.5rem; border: 1px solid #ddd;"><?= $user->id ?></td>
                <td data-style="padding: 0.5rem; border: 1px solid #ddd;"><?= h($user->username) ?></td>
                <td data-style="padding: 0.5rem; border: 1px solid #ddd;"><?= h($user->email) ?></td>
                <td data-style="padding: 0.5rem; border: 1px solid #ddd;"><?= $user->role_id ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } else { ?>
    <p data-style="color: #666; font-style: italic;">No users in demo mode.</p>
    <?php } ?>

    <div data-style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/reports">Reports</a></li>
        </ul>
    </div>
</div>
