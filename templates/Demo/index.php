<?php
/**
 * @var \App\View\AppView $this
 * @var int|null $currentRoleId
 * @var string|null $currentRoleName
 * @var int|null $currentUserId
 * @var string|null $currentUsername
 * @var int|null $currentTeamId
 * @var array $sessionFeatures
 * @var array<\App\Model\Entity\User> $demoUsers
 * @var array<\TinyAuthBackend\Model\Entity\Role> $roles
 */

$allFeatures = ['acl', 'allow', 'roles', 'resources', 'scopes'];
$featureLabels = [
    'acl' => 'ACL (Role Permissions)',
    'allow' => 'Allow (Public Actions)',
    'roles' => 'Roles (Hierarchy)',
    'resources' => 'Resources (Entity-Level)',
    'scopes' => 'Scopes (Conditions)',
];
$roleColors = ['user' => '#4caf50', 'moderator' => '#ff9800', 'admin' => '#f44336'];
?>

<div data-style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <h1>TinyAuth Backend Demo</h1>
    <p data-style="color: #666; margin-bottom: 2rem;">
        Interactive demo for testing TinyAuth features. Switch roles, users, and feature toggles to see how permissions work.
    </p>

    <div data-style="background: #fff4e0; border: 1px solid #e0a800; border-radius: 8px; padding: 1.25rem 1.5rem; margin-bottom: 2rem;">
        <h3 data-style="margin-top: 0; color: #b56a00;">🚀 Four Usage Strategies</h3>
        <p data-style="margin: 0.5rem 0 1rem;">
            Four concrete ways to wire the plugin into a real CakePHP app, each running
            live against the same seed data. Switch roles in the card below, then click
            into a strategy to see how that wiring behaves.
        </p>
        <?= $this->Html->link(
            'Explore all four strategies →',
            ['controller' => 'Strategy', 'action' => 'index'],
            ['data-style' => 'display:inline-block;background:#b56a00;color:#fff;padding:0.5rem 1rem;border-radius:4px;text-decoration:none;font-weight:bold;']
        ) ?>
    </div>

    <div data-style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">

        <!-- Role Switcher -->
        <div data-style="background: #e3f2fd; padding: 1.5rem; border-radius: 8px; border: 1px solid #2196f3;">
            <h3 data-style="margin-top: 0; color: #1565c0;">Role Switcher</h3>
            <p data-style="margin-bottom: 1rem;">
                <strong>Current:</strong>
                <?php if ($currentRoleId) { ?>
                    <span data-style="background: <?= $roleColors[$currentRoleName] ?? '#666' ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 4px;">
                        <?= h($currentRoleName) ?> (ID: <?= $currentRoleId ?>)
                    </span>
                <?php } else { ?>
                    <span data-style="background: #9e9e9e; color: white; padding: 0.25rem 0.75rem; border-radius: 4px;">Not logged in</span>
                <?php } ?>
            </p>

            <div data-style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?= $this->Form->create(null, ['url' => ['controller' => 'RoleSwitcher', 'action' => 'switch'], 'data-style' => 'margin: 0;']) ?>
                    <?= $this->Form->hidden('role_id', ['value' => 0]) ?>
                    <button type="submit" class="btn <?= !$currentRoleId ? 'btn-active' : '' ?>" data-style="background: #9e9e9e;">Guest</button>
                <?= $this->Form->end() ?>

                <?php foreach ($roles as $role) {
                    $color = $roleColors[$role->alias] ?? '#666';
                    $isActive = $currentRoleId == $role->id;
                ?>
                <?= $this->Form->create(null, ['url' => ['controller' => 'RoleSwitcher', 'action' => 'switch'], 'data-style' => 'margin: 0;']) ?>
                    <?= $this->Form->hidden('role_id', ['value' => $role->id]) ?>
                    <?= $this->Form->hidden('role_name', ['value' => $role->alias]) ?>
                    <button type="submit" class="btn <?= $isActive ? 'btn-active' : '' ?>" data-style="background: <?= $color ?>;">
                        <?= h($role->name) ?>
                    </button>
                <?= $this->Form->end() ?>
                <?php } ?>
            </div>
        </div>

        <!-- User Switcher -->
        <div data-style="background: #f3e5f5; padding: 1.5rem; border-radius: 8px; border: 1px solid #9c27b0;">
            <h3 data-style="margin-top: 0; color: #7b1fa2;">User Switcher</h3>
            <p data-style="margin-bottom: 1rem;">
                <strong>Current:</strong>
                <?php if ($currentUserId) { ?>
                    <span data-style="background: #9c27b0; color: white; padding: 0.25rem 0.75rem; border-radius: 4px;">
                        <?= h($currentUsername) ?> (Team: <?= $currentTeamId ?? 'None' ?>)
                    </span>
                <?php } else { ?>
                    <span data-style="background: #9e9e9e; color: white; padding: 0.25rem 0.75rem; border-radius: 4px;">No user</span>
                <?php } ?>
            </p>

            <div data-style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?php foreach ($demoUsers as $user) {
                    $isActive = $currentUserId == $user->id;
                    $teamName = $user->team->name ?? 'No Team';
                ?>
                <?= $this->Form->create(null, ['url' => ['controller' => 'RoleSwitcher', 'action' => 'switchUser'], 'data-style' => 'margin: 0;']) ?>
                    <?= $this->Form->hidden('user_id', ['value' => $user->id]) ?>
                    <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-active' : '' ?>" data-style="background: #9c27b0; line-height: 1.2;">
                        <?= h($user->username) ?><br>
                        <small data-style="opacity: 0.8;"><?= h($teamName) ?></small>
                    </button>
                <?= $this->Form->end() ?>
                <?php } ?>
            </div>
            <p data-style="margin-top: 0.75rem; font-size: 0.85rem; color: #666;">
                Test <a href="/articles">Articles</a> (own scope) and <a href="/projects">Projects</a> (team scope).
            </p>
        </div>

        <!-- Feature Toggles -->
        <div data-style="background: #fff3e0; padding: 1.5rem; border-radius: 8px; border: 1px solid #ff9800;">
            <h3 data-style="margin-top: 0; color: #e65100;">Feature Toggles</h3>
            <p data-style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">
                Override auto-detected feature settings.
            </p>

            <?= $this->Form->create(null, ['url' => ['controller' => 'FeatureSwitcher', 'action' => 'update']]) ?>
            <div data-style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                <?php foreach ($allFeatures as $feature) {
                    $isEnabled = $sessionFeatures[$feature] ?? true;
                    $isOverridden = isset($sessionFeatures[$feature]);
                ?>
                <label data-style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background: <?= $isEnabled ? '#e8f5e9' : '#ffebee' ?>; border-radius: 4px; cursor: pointer; <?= $isOverridden ? 'border: 2px solid #ff9800;' : 'border: 2px solid transparent;' ?>">
                    <input type="hidden" name="<?= $feature ?>" value="0">
                    <input type="checkbox" name="<?= $feature ?>" value="1" <?= $isEnabled ? 'checked' : '' ?> data-style="width: 18px; height: 18px;">
                    <span><?= h($featureLabels[$feature]) ?></span>
                </label>
                <?php } ?>
            </div>
            <div data-style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn" data-style="background: #ff9800;">Apply</button>
                <?= $this->Form->end() ?>
                <?= $this->Form->create(null, ['url' => ['controller' => 'FeatureSwitcher', 'action' => 'reset'], 'data-style' => 'margin: 0;']) ?>
                <button type="submit" class="btn" data-style="background: #9e9e9e;">Reset</button>
                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- Admin Panel Links -->
        <div data-style="background: #e8f5e9; padding: 1.5rem; border-radius: 8px; border: 1px solid #4caf50;">
            <h3 data-style="margin-top: 0; color: #2e7d32;">Admin Panel</h3>
            <ul data-style="list-style: none; padding: 0; margin: 0;">
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth"><strong>Dashboard</strong></a> - Overview</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth/acl">ACL Permissions</a> - Permission matrix</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth/allow">Allow (Public)</a> - Public actions</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth/roles">Roles</a> - Role hierarchy</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth/resources">Resources</a> - Entity permissions</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth/scopes">Scopes</a> - Conditions</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/auth/sync">Sync</a> - Discover controllers</li>
            </ul>
        </div>

        <!-- Sample Controllers -->
        <div data-style="background: #fce4ec; padding: 1.5rem; border-radius: 8px; border: 1px solid #e91e63;">
            <h3 data-style="margin-top: 0; color: #c2185b;">Test Controllers</h3>
            <ul data-style="list-style: none; padding: 0; margin: 0;">
                <li data-style="margin-bottom: 0.5rem;"><a href="/dashboard">Dashboard</a> - Any authenticated user</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/reports">Reports</a> - Moderator or admin</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/admin/users">Admin: Users</a> - Admin only</li>
            </ul>
            <h4 data-style="margin-top: 1rem; margin-bottom: 0.5rem; color: #c2185b;">Resource Demos</h4>
            <ul data-style="list-style: none; padding: 0; margin: 0;">
                <li data-style="margin-bottom: 0.5rem;"><a href="/articles"><strong>Articles</strong></a> - "own" scope demo</li>
                <li data-style="margin-bottom: 0.5rem;"><a href="/projects"><strong>Projects</strong></a> - "team" scope demo</li>
            </ul>
        </div>

        <!-- Documentation -->
        <div data-style="background: #e0f7fa; padding: 1.5rem; border-radius: 8px; border: 1px solid #00bcd4;">
            <h3 data-style="margin-top: 0; color: #00838f;">Documentation</h3>
            <ul data-style="list-style: none; padding: 0; margin: 0;">
                <li data-style="margin-bottom: 0.5rem;">
                    <a href="/admin/auth/dashboard/concepts" data-style="font-weight: bold;">TinyAuth Concepts</a>
                    - How ACL, Allow, Roles, Resources, and Scopes work
                </li>
                <li>
                    <a href="https://github.com/dereuromark/cakephp-tinyauth-backend/tree/master/docs" target="_blank">Full Documentation</a>
                    - GitHub docs
                </li>
            </ul>
        </div>

    </div>
</div>

<?php $cspNonce = (string)$this->getRequest()->getAttribute('cspNonce', ''); ?>
<style<?= $cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '' ?>>
.btn {
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}
.btn:hover { opacity: 0.9; }
.btn-active { outline: 3px solid #333; }
.btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
</style>
