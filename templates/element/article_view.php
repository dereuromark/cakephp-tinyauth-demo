<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var array<string, string>|null $strategy
 */
?>
<?= $this->element('strategy_banner', ['strategy' => $strategy]) ?>
<h1><?= h($article->title) ?></h1>
<p class="meta">by <strong><?= h($article->user->username ?? '?') ?></strong> · <?= h($article->status) ?></p>
<div class="article-body"><?= nl2br(h($article->body ?? '')) ?></div>
<p class="nav-links">
    <?= $this->Html->link('← Back to articles', ['action' => 'index']) ?>
    &middot;
    <?= $this->Html->link('Edit', ['action' => 'edit', $article->id]) ?>
</p>
