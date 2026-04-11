<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Project $project
 * @var array<string, string>|null $strategy
 */
?>
<?= $this->element('strategy_banner', ['strategy' => $strategy]) ?>
<h1><?= h($project->name) ?></h1>
<p class="meta">
    owner: <strong><?= h($project->user->username ?? '?') ?></strong>
    · team: <strong><?= h($project->team->name ?? '—') ?></strong>
</p>
<div class="project-body"><?= nl2br(h($project->description ?? '')) ?></div>
<p class="nav-links">
    <?= $this->Html->link('← Back to projects', ['action' => 'index']) ?>
    &middot;
    <?= $this->Html->link('Edit', ['action' => 'edit', $project->id]) ?>
</p>
