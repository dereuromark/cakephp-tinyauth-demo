<?php
/**
 * @var \App\View\AppView $this
 * @var array<\App\Model\Entity\Project> $projects
 * @var array<string, string>|null $strategy
 */
?>
<?= $this->element('strategy_banner', ['strategy' => $strategy]) ?>
<h1>Projects</h1>
<p class="note">
    <?php if (($strategy['slug'] ?? null) === \App\Service\Strategy::ADAPTER_ONLY): ?>
        AdapterOnly shows every project; no entity-level scope is applied.
    <?php else: ?>
        Projects exercise the <code>team</code> scope
        (<code>projects.team_id = users.team_id</code>) in addition to
        <code>own</code>. Regular users see projects from their own team;
        moderators see everything in their team and own projects from
        other teams.
    <?php endif; ?>
</p>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Owner</th>
            <th>Team</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project): ?>
            <tr>
                <td><?= $this->Html->link($project->name, ['action' => 'view', $project->id]) ?></td>
                <td><?= h($project->user->username ?? '?') ?></td>
                <td><?= h($project->team->name ?? '—') ?></td>
                <td>
                    <?= $this->Html->link('View', ['action' => 'view', $project->id]) ?>
                    &middot;
                    <?= $this->Html->link('Edit', ['action' => 'edit', $project->id]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
            <tr><td colspan="4" class="empty">No projects visible under current role + strategy.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
