<?php
/**
 * Articles index - demonstrates resource-level permissions with "own" scope.
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var \App\Model\Entity\Article[]|\Cake\Collection\CollectionInterface $articles
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 * @var array|null $conditions
 */
?>
<div style="max-width: 1000px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 style="margin-top: 0; color: #1565c0;">Resource Permission Demo: Articles</h3>
        <p style="margin-bottom: 0.5rem;">
            <strong>Current Role:</strong> <?= h($currentRole['name'] ?? 'None') ?>
            | <strong>User ID:</strong> <?= $currentUser ? $currentUser->id : 'N/A' ?>
        </p>
        <p style="margin-bottom: 0;">
            <strong>Scope Applied:</strong>
            <?php if ($conditions === null) { ?>
                <span style="color: #c62828;">No access (cannot view any articles)</span>
            <?php } elseif ($conditions) { ?>
                <span style="color: #f57c00;">Scoped: <?= h(json_encode($conditions)) ?></span>
            <?php } else { ?>
                <span style="color: #2e7d32;">Full access (no restrictions)</span>
            <?php } ?>
        </p>
    </div>

    <div style="background: #fff3e0; border: 1px solid #ff9800; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h4 style="margin-top: 0;">Permission Rules for Articles</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <tr style="background: #ffe0b2;">
                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">Role</th>
                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">View</th>
                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">Edit</th>
                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">Delete</th>
            </tr>
            <tr>
                <td style="padding: 0.5rem;">User</td>
                <td style="padding: 0.5rem;">All</td>
                <td style="padding: 0.5rem; color: #f57c00;">Own only</td>
                <td style="padding: 0.5rem; color: #f57c00;">Own only</td>
            </tr>
            <tr style="background: #fff8e1;">
                <td style="padding: 0.5rem;">Moderator</td>
                <td style="padding: 0.5rem;">All</td>
                <td style="padding: 0.5rem;">All</td>
                <td style="padding: 0.5rem; color: #f57c00;">Own only</td>
            </tr>
            <tr>
                <td style="padding: 0.5rem;">Admin</td>
                <td style="padding: 0.5rem;">All</td>
                <td style="padding: 0.5rem;">All</td>
                <td style="padding: 0.5rem;">All</td>
            </tr>
        </table>
    </div>

    <?php if (!$articles) { ?>
        <p style="color: #666; font-style: italic;">No articles found (or no permission to view).</p>
    <?php } else { ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Title</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Author</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Owner?</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article) { ?>
                    <?php $isOwner = $currentUser && $article->user_id === $currentUser->id; ?>
                    <tr style="<?= $isOwner ? 'background: #e8f5e9;' : '' ?>">
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $this->Html->link($article->title, ['action' => 'view', $article->id]) ?>
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= h($article->user->username ?? 'Unknown') ?>
                            (ID: <?= $article->user_id ?>)
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; <?= $article->status === 'published' ? 'background: #c8e6c9; color: #2e7d32;' : 'background: #fff9c4; color: #f57f17;' ?>">
                                <?= h($article->status) ?>
                            </span>
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $isOwner ? '<span style="color: #2e7d32;">Yes</span>' : '<span style="color: #9e9e9e;">No</span>' ?>
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $this->Html->link('View', ['action' => 'view', $article->id], ['style' => 'margin-right: 0.5rem;']) ?>
                            <?php if ($article->canEdit) { ?>
                                <?= $this->Html->link('Edit', ['action' => 'edit', $article->id], ['style' => 'margin-right: 0.5rem;']) ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><?= $this->Html->link('Projects (Team Scope)', ['controller' => 'Projects', 'action' => 'index']) ?></li>
            <li><a href="/admin/auth/resources">Manage Resource Permissions</a></li>
        </ul>
    </div>
</div>
