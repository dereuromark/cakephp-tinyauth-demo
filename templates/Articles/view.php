<?php
/**
 * Article view - demonstrates entity-level permission checks.
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var \App\Model\Entity\Article $article
 * @var bool $canView
 * @var bool $canEdit
 * @var bool $canDelete
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 */

$isOwner = $currentUser && $article->user_id === $currentUser->id;
?>
<div data-style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div data-style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 data-style="margin-top: 0; color: #1565c0;">Permission Check Results</h3>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Current Role:</strong> <?= h($currentRole['name'] ?? 'None') ?>
            | <strong>User ID:</strong> <?= $currentUser ? $currentUser->id : 'N/A' ?>
        </p>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Article Owner:</strong> <?= h($article->user->username ?? 'Unknown') ?> (ID: <?= $article->user_id ?>)
            | <strong>You Own This:</strong> <?= $isOwner ? '<span data-style="color: #2e7d32;">Yes</span>' : '<span data-style="color: #c62828;">No</span>' ?>
        </p>
        <p data-style="margin-bottom: 0;">
            <strong>Permissions:</strong>
            <span data-style="margin-left: 0.5rem; padding: 0.25rem 0.5rem; border-radius: 4px; <?= $canView ? 'background: #c8e6c9; color: #2e7d32;' : 'background: #ffcdd2; color: #c62828;' ?>">
                View: <?= $canView ? 'Yes' : 'No' ?>
            </span>
            <span data-style="margin-left: 0.5rem; padding: 0.25rem 0.5rem; border-radius: 4px; <?= $canEdit ? 'background: #c8e6c9; color: #2e7d32;' : 'background: #ffcdd2; color: #c62828;' ?>">
                Edit: <?= $canEdit ? 'Yes' : 'No' ?>
            </span>
            <span data-style="margin-left: 0.5rem; padding: 0.25rem 0.5rem; border-radius: 4px; <?= $canDelete ? 'background: #c8e6c9; color: #2e7d32;' : 'background: #ffcdd2; color: #c62828;' ?>">
                Delete: <?= $canDelete ? 'Yes' : 'No' ?>
            </span>
        </p>
    </div>

    <div data-style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; <?= $isOwner ? 'border-left: 4px solid #4caf50;' : '' ?>">
        <h2 data-style="margin-top: 0;"><?= h($article->title) ?></h2>

        <div data-style="color: #666; margin-bottom: 1rem;">
            <span data-style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; <?= $article->status === 'published' ? 'background: #c8e6c9; color: #2e7d32;' : 'background: #fff9c4; color: #f57f17;' ?>">
                <?= h($article->status) ?>
            </span>
            | By <?= h($article->user->username ?? 'Unknown') ?>
            | Created <?= $article->created->nice() ?>
        </div>

        <div data-style="line-height: 1.6;">
            <?= nl2br(h($article->body)) ?>
        </div>
    </div>

    <div data-style="margin-bottom: 1.5rem;">
        <?php if ($canEdit) { ?>
            <?= $this->Html->link('Edit Article', ['action' => 'edit', $article->id], [
                'data-style' => 'display: inline-block; padding: 0.5rem 1rem; background: #2196f3; color: white; text-decoration: none; border-radius: 4px; margin-right: 0.5rem;',
            ]) ?>
        <?php } else { ?>
            <span data-style="display: inline-block; padding: 0.5rem 1rem; background: #ccc; color: #666; border-radius: 4px; margin-right: 0.5rem; cursor: not-allowed;">
                Edit (No Permission)
            </span>
        <?php } ?>

        <?php if ($canDelete) { ?>
            <?= $this->Form->postLink('Delete Article', ['action' => 'delete', $article->id], [
                'confirm' => 'Are you sure?',
                'data-style' => 'display: inline-block; padding: 0.5rem 1rem; background: #f44336; color: white; text-decoration: none; border-radius: 4px;',
                'block' => true,
            ]) ?>
        <?php } else { ?>
            <span data-style="display: inline-block; padding: 0.5rem 1rem; background: #ccc; color: #666; border-radius: 4px; cursor: not-allowed;">
                Delete (No Permission)
            </span>
        <?php } ?>
    </div>

    <div data-style="background: #f5f5f5; padding: 1rem; border-radius: 8px;">
        <h3>Navigation</h3>
        <ul>
            <li><?= $this->Html->link('Back to Articles', ['action' => 'index']) ?></li>
            <li><a href="/">Homepage</a></li>
        </ul>
    </div>
</div>
