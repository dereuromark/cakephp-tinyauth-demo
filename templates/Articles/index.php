<?php
/**
 * Articles index - demonstrates resource-level permissions with "own" scope.
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var array<\App\Model\Entity\Article>|\Cake\Collection\CollectionInterface<\App\Model\Entity\Article> $articles
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 * @var array|null $conditions
 */
?>
<div data-style="max-width: 1000px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div data-style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 data-style="margin-top: 0; color: #1565c0;">Resource Permission Demo: Articles</h3>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Current Role:</strong> <?= h($currentRole['name'] ?? 'None') ?>
            | <strong>User ID:</strong> <?= $currentUser ? $currentUser->id : 'N/A' ?>
        </p>
        <p data-style="margin-bottom: 0;">
            <strong>Scope Applied:</strong>
            <?php if ($conditions === null) { ?>
                <span data-style="color: #c62828;">No access (cannot view any articles)</span>
            <?php } elseif ($conditions) { ?>
                <span data-style="color: #f57c00;">Scoped: <?= h(json_encode($conditions)) ?></span>
            <?php } else { ?>
                <span data-style="color: #2e7d32;">Full access (no restrictions)</span>
            <?php } ?>
        </p>
    </div>

    <div data-style="background: #fff3e0; border: 1px solid #ff9800; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h4 data-style="margin-top: 0;">Permission Rules for Articles</h4>
        <table data-style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <tr data-style="background: #ffe0b2;">
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">Role</th>
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">View</th>
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">Edit</th>
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #ff9800;">Delete</th>
            </tr>
            <tr>
                <td data-style="padding: 0.5rem;">User</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem; color: #f57c00;">Own only</td>
                <td data-style="padding: 0.5rem; color: #f57c00;">Own only</td>
            </tr>
            <tr data-style="background: #fff8e1;">
                <td data-style="padding: 0.5rem;">Moderator</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem; color: #f57c00;">Own only</td>
            </tr>
            <tr>
                <td data-style="padding: 0.5rem;">Admin</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem;">All</td>
            </tr>
        </table>
    </div>

    <?php if (!$articles) { ?>
        <p data-style="color: #666; font-style: italic;">No articles found (or no permission to view).</p>
    <?php } else { ?>
        <table data-style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr data-style="background: #f5f5f5;">
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Title</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Author</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Owner?</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article) { ?>
                    <?php $isOwner = $currentUser && $article->user_id === $currentUser->id; ?>
                    <tr data-style="<?= $isOwner ? 'background: #e8f5e9;' : '' ?>">
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $this->Html->link($article->title, ['action' => 'view', $article->id]) ?>
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= h($article->user->username ?? 'Unknown') ?>
                            (ID: <?= $article->user_id ?>)
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <span data-style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; <?= $article->status === 'published' ? 'background: #c8e6c9; color: #2e7d32;' : 'background: #fff9c4; color: #f57f17;' ?>">
                                <?= h($article->status) ?>
                            </span>
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $isOwner ? '<span data-style="color: #2e7d32;">Yes</span>' : '<span data-style="color: #9e9e9e;">No</span>' ?>
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $this->Html->link('View', ['action' => 'view', $article->id], ['data-style' => 'margin-right: 0.5rem;']) ?>
                            <?php if ($article->canEdit) { ?>
                                <?= $this->Html->link('Edit', ['action' => 'edit', $article->id], ['data-style' => 'margin-right: 0.5rem;']) ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <div data-style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><?= $this->Html->link('Projects (Team Scope)', ['controller' => 'Projects', 'action' => 'index']) ?></li>
            <li><a href="/admin/auth/resources">Manage Resource Permissions</a></li>
        </ul>
    </div>
</div>
