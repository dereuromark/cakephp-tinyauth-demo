<?php
/**
 * Article edit - user must have "edit" ability (with scope check).
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var \App\Model\Entity\Article $article
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 */

$isOwner = $currentUser && $article->user_id === $currentUser->id;
?>
<div style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div style="background: #e8f5e9; border: 1px solid #4caf50; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 style="margin-top: 0; color: #2e7d32;">Edit Access Granted</h3>
        <p style="margin-bottom: 0.5rem;">
            You have permission to edit this article because:
        </p>
        <ul style="margin-bottom: 0;">
            <?php if ($isOwner) { ?>
                <li>You are the owner (your user_id = article.user_id)</li>
            <?php } ?>
            <?php if (in_array($currentRole['name'] ?? '', ['Moderator', 'Administrator'])) { ?>
                <li>Your role (<?= h($currentRole['name']) ?>) has edit permission without scope restriction</li>
            <?php } ?>
        </ul>
    </div>

    <?= $this->Form->create($article) ?>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Title</label>
            <?= $this->Form->control('title', [
                'label' => false,
                'style' => 'width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;',
            ]) ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Body</label>
            <?= $this->Form->control('body', [
                'type' => 'textarea',
                'label' => false,
                'rows' => 6,
                'style' => 'width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;',
            ]) ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Status</label>
            <?= $this->Form->control('status', [
                'type' => 'select',
                'options' => ['draft' => 'Draft', 'published' => 'Published'],
                'label' => false,
                'style' => 'padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;',
            ]) ?>
        </div>

        <div style="margin-top: 1.5rem;">
            <?= $this->Form->button('Save Article', [
                'style' => 'padding: 0.75rem 1.5rem; background: #4caf50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;',
            ]) ?>
            <?= $this->Html->link('Cancel', ['action' => 'view', $article->id], [
                'style' => 'margin-left: 1rem; color: #666;',
            ]) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
