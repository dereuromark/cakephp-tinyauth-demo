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
        <?= $this->Form->create(null, [
            'url' => [
                'prefix' => false,
                'controller' => 'ExternalRoleSwitcher',
                'action' => 'switch',
            ],
            'class' => 'external-role-switcher__form',
        ]) ?>
        <?= $this->Form->hidden('role', ['value' => $role]) ?>
        <button
            type="submit"
            class="ext-role-btn <?= (($externalRole ?? null) === $role ? 'ext-role-btn--active' : '') ?>"
        ><?= h(ucfirst($role)) ?></button>
        <?= $this->Form->end() ?>
    <?php endforeach; ?>
    <?= $this->Form->create(null, [
        'url' => [
            'prefix' => false,
            'controller' => 'ExternalRoleSwitcher',
            'action' => 'switch',
        ],
        'class' => 'external-role-switcher__form',
    ]) ?>
    <?= $this->Form->hidden('role', ['value' => '']) ?>
    <button type="submit" class="ext-role-btn ext-role-btn--clear">clear</button>
    <?= $this->Form->end() ?>
</div>
<style>
.external-role-switcher { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #fffbea; border-left: 4px solid #d80; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem; }
.external-role-switcher__form { margin: 0; }
.ext-role-btn { padding: 0.2rem 0.6rem; border: 1px solid #aaa; border-radius: 3px; text-decoration: none; font-size: 0.85rem; background: #fff; }
.ext-role-btn--active { background: #d80; color: #fff; border-color: #d80; }
.ext-role-btn--clear { opacity: 0.6; }
</style>
