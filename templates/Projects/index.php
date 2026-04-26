<?php
/**
 * Projects index - demonstrates resource-level permissions with "team" scope.
 *
 * @var \App\View\AppView $this
 * @var string $pageTitle
 * @var array<\App\Model\Entity\Project>|\Cake\Collection\CollectionInterface<\App\Model\Entity\Project> $projects
 * @var \Cake\ORM\Entity|null $currentUser
 * @var array $currentRole
 * @var array|null $conditions
 */
?>
<div data-style="max-width: 1000px; margin: 0 auto; padding: 2rem;">
    <h1><?= h($pageTitle) ?></h1>

    <div data-style="background: #f3e5f5; border: 1px solid #9c27b0; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 data-style="margin-top: 0; color: #7b1fa2;">Resource Permission Demo: Projects (Team Scope)</h3>
        <p data-style="margin-bottom: 0.5rem;">
            <strong>Current Role:</strong> <?= h($currentRole['name'] ?? 'None') ?>
            | <strong>User ID:</strong> <?= $currentUser ? $currentUser->id : 'N/A' ?>
            | <strong>Team ID:</strong> <?= $currentUser && $currentUser->team_id ? $currentUser->team_id : 'None' ?>
        </p>
        <p data-style="margin-bottom: 0;">
            <strong>View Scope Applied:</strong>
            <?php if ($conditions === null) { ?>
                <span data-style="color: #c62828;">No access</span>
            <?php } elseif ($conditions) { ?>
                <span data-style="color: #7b1fa2;">Team-scoped: <?= h(json_encode($conditions)) ?></span>
            <?php } else { ?>
                <span data-style="color: #2e7d32;">Full access (no restrictions)</span>
            <?php } ?>
        </p>
    </div>

    <div data-style="background: #fce4ec; border: 1px solid #e91e63; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h4 data-style="margin-top: 0;">Permission Rules for Projects</h4>
        <table data-style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <tr data-style="background: #f8bbd0;">
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #e91e63;">Role</th>
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #e91e63;">View</th>
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #e91e63;">Edit</th>
                <th data-style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #e91e63;">Delete</th>
            </tr>
            <tr>
                <td data-style="padding: 0.5rem;">User</td>
                <td data-style="padding: 0.5rem; color: #7b1fa2;">Team only</td>
                <td data-style="padding: 0.5rem; color: #f57c00;">Own only</td>
                <td data-style="padding: 0.5rem; color: #c62828;">None</td>
            </tr>
            <tr data-style="background: #fce4ec;">
                <td data-style="padding: 0.5rem;">Moderator</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem; color: #7b1fa2;">Team only</td>
                <td data-style="padding: 0.5rem; color: #f57c00;">Own only</td>
            </tr>
            <tr>
                <td data-style="padding: 0.5rem;">Admin</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem;">All</td>
                <td data-style="padding: 0.5rem;">All</td>
            </tr>
        </table>
    </div>

    <?php if (!$projects) { ?>
        <p data-style="color: #666; font-style: italic;">No projects found (or no permission to view).</p>
    <?php } else { ?>
        <table data-style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr data-style="background: #f5f5f5;">
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Project</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Owner</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Team</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Your Access</th>
                    <th data-style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project) { ?>
                    <?php
                    $isOwner = $currentUser && $project->user_id === $currentUser->id;
                    $isSameTeam = $currentUser && $currentUser->team_id && $project->team_id === $currentUser->team_id;
                    ?>
                    <tr data-style="<?= $isOwner ? 'background: #e8f5e9;' : ($isSameTeam ? 'background: #f3e5f5;' : '') ?>">
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $this->Html->link($project->name, ['action' => 'view', $project->id]) ?>
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= h($project->user->username ?? 'Unknown') ?>
                            (ID: <?= $project->user_id ?>)
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= h($project->team->name ?? 'No team') ?>
                            (ID: <?= $project->team_id ?? '-' ?>)
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?php if ($isOwner) { ?>
                                <span data-style="color: #2e7d32;">Owner</span>
                            <?php } elseif ($isSameTeam) { ?>
                                <span data-style="color: #7b1fa2;">Team</span>
                            <?php } else { ?>
                                <span data-style="color: #9e9e9e;">None</span>
                            <?php } ?>
                        </td>
                        <td data-style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                            <?= $this->Html->link('View', ['action' => 'view', $project->id], ['data-style' => 'margin-right: 0.5rem;']) ?>
                            <?php if ($project->canEdit) { ?>
                                <?= $this->Html->link('Edit', ['action' => 'edit', $project->id], ['data-style' => 'margin-right: 0.5rem;']) ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <div data-style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
        <h3>Navigation</h3>
        <ul>
            <li><a href="/">Back to Homepage</a></li>
            <li><?= $this->Html->link('Articles (Own Scope)', ['controller' => 'Articles', 'action' => 'index']) ?></li>
            <li><a href="/admin/auth/resources">Manage Resource Permissions</a></li>
        </ul>
    </div>
</div>
