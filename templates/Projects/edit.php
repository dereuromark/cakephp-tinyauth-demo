<?php
/**
 * Project edit - user must have "edit" ability (with scope check).
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var \App\Model\Entity\Project $project
 * @var array $teams
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 */

$isOwner = $currentUser && $project->user_id === $currentUser->id;
$isSameTeam = $currentUser && $currentUser->team_id && $project->team_id === $currentUser->team_id;
?>
<div style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div style="background: #e8f5e9; border: 1px solid #4caf50; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 style="margin-top: 0; color: #2e7d32;">Edit Access Granted</h3>
        <p style="margin-bottom: 0.5rem;">
            You have permission to edit this project because:
        </p>
        <ul style="margin-bottom: 0;">
            <?php if ($isOwner) { ?>
                <li>You are the owner (your user_id = project.user_id)</li>
            <?php } ?>
            <?php if ($isSameTeam && !$isOwner) { ?>
                <li>You are on the same team (your team_id = project.team_id)</li>
            <?php } ?>
            <?php if ($currentRole['name'] === 'Administrator') { ?>
                <li>Your role (Administrator) has full edit access</li>
            <?php } ?>
        </ul>
    </div>

    <?= $this->Form->create($project) ?>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Project Name</label>
            <?= $this->Form->control('name', [
                'label' => false,
                'style' => 'width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;',
            ]) ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Description</label>
            <?= $this->Form->control('description', [
                'type' => 'textarea',
                'label' => false,
                'rows' => 6,
                'style' => 'width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;',
            ]) ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Team</label>
            <?= $this->Form->control('team_id', [
                'type' => 'select',
                'options' => $teams,
                'empty' => '-- No Team --',
                'label' => false,
                'style' => 'padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;',
            ]) ?>
        </div>

        <div style="margin-top: 1.5rem;">
            <?= $this->Form->button('Save Project', [
                'style' => 'padding: 0.75rem 1.5rem; background: #9c27b0; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;',
            ]) ?>
            <?= $this->Html->link('Cancel', ['action' => 'view', $project->id], [
                'style' => 'margin-left: 1rem; color: #666;',
            ]) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
