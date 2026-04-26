<?php
/**
 * Project view - demonstrates entity-level permission checks with team scope.
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var \App\Model\Entity\Project $project
 * @var bool $canView
 * @var bool $canEdit
 * @var bool $canDelete
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 */

$isOwner = $currentUser && $project->user_id === $currentUser->id;
$isSameTeam = $currentUser && $currentUser->team_id && $project->team_id === $currentUser->team_id;
?>
<div data-style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div data-style="background: #f3e5f5; border: 1px solid #9c27b0; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 data-style="margin-top: 0; color: #7b1fa2;">Permission Check Results</h3>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Current Role:</strong> <?= h($currentRole['name'] ?? 'None') ?>
            | <strong>User ID:</strong> <?= $currentUser ? $currentUser->id : 'N/A' ?>
            | <strong>Your Team ID:</strong> <?= $currentUser && $currentUser->team_id ? $currentUser->team_id : 'None' ?>
        </p>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Project Owner:</strong> <?= h($project->user->username ?? 'Unknown') ?> (ID: <?= $project->user_id ?>)
            | <strong>Project Team:</strong> <?= h($project->team->name ?? 'None') ?> (ID: <?= $project->team_id ?? '-' ?>)
        </p>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Your Relationship:</strong>
            <?php if ($isOwner) { ?>
                <span data-style="padding: 0.25rem 0.5rem; background: #c8e6c9; color: #2e7d32; border-radius: 4px;">Owner</span>
            <?php } elseif ($isSameTeam) { ?>
                <span data-style="padding: 0.25rem 0.5rem; background: #e1bee7; color: #7b1fa2; border-radius: 4px;">Same Team</span>
            <?php } else { ?>
                <span data-style="padding: 0.25rem 0.5rem; background: #ffcdd2; color: #c62828; border-radius: 4px;">No Relationship</span>
            <?php } ?>
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

    <div data-style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; <?= $isOwner ? 'border-left: 4px solid #4caf50;' : ($isSameTeam ? 'border-left: 4px solid #9c27b0;' : '') ?>">
        <h2 data-style="margin-top: 0;"><?= h($project->name) ?></h2>

        <div data-style="color: #666; margin-bottom: 1rem;">
            <span data-style="padding: 0.25rem 0.5rem; background: #e1bee7; color: #7b1fa2; border-radius: 4px; font-size: 0.8rem;">
                <?= h($project->team->name ?? 'No Team') ?>
            </span>
            | Owner: <?= h($project->user->username ?? 'Unknown') ?>
            | Created <?= $project->created->nice() ?>
        </div>

        <div data-style="line-height: 1.6;">
            <?= nl2br(h($project->description ?? 'No description')) ?>
        </div>
    </div>

    <div data-style="margin-bottom: 1.5rem;">
        <?php if ($canEdit) { ?>
            <?= $this->Html->link('Edit Project', ['action' => 'edit', $project->id], [
                'data-style' => 'display: inline-block; padding: 0.5rem 1rem; background: #9c27b0; color: white; text-decoration: none; border-radius: 4px; margin-right: 0.5rem;',
            ]) ?>
        <?php } else { ?>
            <span data-style="display: inline-block; padding: 0.5rem 1rem; background: #ccc; color: #666; border-radius: 4px; margin-right: 0.5rem; cursor: not-allowed;">
                Edit (No Permission)
            </span>
        <?php } ?>

        <?php if ($canDelete) { ?>
            <?= $this->Form->postLink('Delete Project', ['action' => 'delete', $project->id], [
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
            <li><?= $this->Html->link('Back to Projects', ['action' => 'index']) ?></li>
            <li><a href="/">Homepage</a></li>
        </ul>
    </div>
</div>
