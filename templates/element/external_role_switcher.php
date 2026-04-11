<?php
/**
 * @var \App\View\AppView $this
 * @var string|null $externalRole
 */
$roles = ['user', 'moderator', 'admin'];
?>
<div class="external-role-switcher">
    <span>Effective role (from session, not <code>users.role_id</code>):</span>
    <?php foreach ($roles as $role): ?>
        <?= $this->Html->link(ucfirst($role), [
            'prefix' => false,
            'controller' => 'ExternalRoleSwitcher',
            'action' => 'switch',
            '?' => ['role' => $role],
        ], [
            'class' => 'ext-role-btn ' . (($externalRole ?? null) === $role ? 'ext-role-btn--active' : ''),
        ]) ?>
    <?php endforeach; ?>
    <?= $this->Html->link('clear', [
        'prefix' => false,
        'controller' => 'ExternalRoleSwitcher',
        'action' => 'switch',
        '?' => ['role' => ''],
    ], ['class' => 'ext-role-btn ext-role-btn--clear']) ?>
</div>
<style>
.external-role-switcher { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #fffbea; border-left: 4px solid #d80; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem; }
.ext-role-btn { padding: 0.2rem 0.6rem; border: 1px solid #aaa; border-radius: 3px; text-decoration: none; font-size: 0.85rem; background: #fff; }
.ext-role-btn--active { background: #d80; color: #fff; border-color: #d80; }
.ext-role-btn--clear { opacity: 0.6; }
</style>
