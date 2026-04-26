<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, array<string, string>> $strategies
 */
$this->assign('title', 'Usage Strategies');
?>
<h1>Usage Strategies</h1>
<p>
    Four ways to wire <code>cakephp-tinyauth-backend</code> into a real
    CakePHP app, each exercised live against the same demo data. Pick a
    card below — the admin UI at <a href="/admin/auth/">/admin/auth/</a>
    edits the rules that all four strategies read from.
</p>

<div class="strategies">
    <?php foreach ($strategies as $slug => $s): ?>
        <div class="strategy-card strategy-card--<?= h($s['variant']) ?>">
            <div class="strategy-card__header">
                <span class="strategy-card__icon"><?= h($s['icon']) ?></span>
                <h2 class="strategy-card__title"><?= h($s['title']) ?></h2>
            </div>
            <div class="strategy-card__tagline"><?= h($s['tagline']) ?></div>
            <p class="strategy-card__summary"><?= h($s['summary']) ?></p>
            <div class="strategy-card__actions">
                <?= $this->Html->link('Articles →', ['prefix' => $s['prefix'], 'controller' => 'Articles', 'action' => 'index'], ['class' => 'button']) ?>
                <?= $this->Html->link('Projects →', ['prefix' => $s['prefix'], 'controller' => 'Projects', 'action' => 'index'], ['class' => 'button button-outline']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<p class="nav-links">
    ← <?= $this->Html->link('Back to Demo Home', ['controller' => 'Demo', 'action' => 'index']) ?>
</p>

<?php $cspNonce = (string)$this->getRequest()->getAttribute('cspNonce', ''); ?>
<style<?= $cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '' ?>>
.strategies { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin: 2rem 0; }
.strategy-card { border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; background: #fafafa; display: flex; flex-direction: column; }
.strategy-card__header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; }
.strategy-card__icon { font-size: 2rem; }
.strategy-card__title { margin: 0; font-size: 1.5rem; }
.strategy-card__tagline { font-size: 0.9rem; font-style: italic; opacity: 0.75; margin-bottom: 0.75rem; }
.strategy-card__summary { flex: 1; font-size: 0.92rem; line-height: 1.4; }
.strategy-card__actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
.strategy-card--info    { border-left: 4px solid #0aa; }
.strategy-card--success { border-left: 4px solid #0a0; }
.strategy-card--primary { border-left: 4px solid #06c; }
.strategy-card--warning { border-left: 4px solid #d80; }
</style>
