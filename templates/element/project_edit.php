<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Project $project
 * @var array<string, string>|null $strategy
 */
?>
<?= $this->element('strategy_banner', ['strategy' => $strategy]) ?>
<h1>Edit project</h1>
<?= $this->Form->create($project) ?>
<?= $this->Form->control('name') ?>
<?= $this->Form->control('description', ['type' => 'textarea', 'rows' => 8]) ?>
<?= $this->Form->button(__('Save')) ?>
<?= $this->Form->end() ?>
