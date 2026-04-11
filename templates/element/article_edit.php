<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var array<string, string>|null $strategy
 */
?>
<?= $this->element('strategy_banner', ['strategy' => $strategy]) ?>
<h1>Edit article</h1>
<?= $this->Form->create($article) ?>
<?= $this->Form->control('title') ?>
<?= $this->Form->control('body', ['type' => 'textarea', 'rows' => 8]) ?>
<?= $this->Form->control('status') ?>
<?= $this->Form->button(__('Save')) ?>
<?= $this->Form->end() ?>
<p class="nav-links">
    <?= $this->Html->link('← Back to view', ['action' => 'view', $article->id]) ?>
</p>
