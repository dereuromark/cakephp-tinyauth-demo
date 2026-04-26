<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, string>|null $strategy
 */
if (empty($strategy)) {
    return;
}
?>
<div class="strategy-banner strategy-banner--<?= h($strategy['variant']) ?>">
    <strong><?= h($strategy['icon']) ?> <?= h($strategy['title']) ?></strong>
    <span class="strategy-banner__tagline"><?= h($strategy['tagline']) ?></span>
    <span class="strategy-banner__nav">
        <?= $this->Html->link('← All strategies', ['prefix' => false, 'controller' => 'Strategy', 'action' => 'index']) ?>
    </span>
</div>
<?php if (($strategy['slug'] ?? null) === \App\Service\Strategy::EXTERNAL_ROLES): ?>
    <?= $this->element('external_role_switcher') ?>
<?php endif; ?>
<?php $cspNonce = (string)$this->getRequest()->getAttribute('cspNonce', ''); ?>
<style<?= $cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '' ?>>
.strategy-banner { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 6px; margin: 0 0 1rem; background: #f2f2f2; border-left: 4px solid #666; }
.strategy-banner--info    { border-left-color: #0aa; }
.strategy-banner--success { border-left-color: #0a0; }
.strategy-banner--primary { border-left-color: #06c; }
.strategy-banner--warning { border-left-color: #d80; }
.strategy-banner__tagline { flex: 1; opacity: 0.75; font-style: italic; font-size: 0.9rem; }
.strategy-banner__nav a { font-size: 0.85rem; }
</style>
