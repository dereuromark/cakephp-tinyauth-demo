<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article[]|\Cake\Collection\CollectionInterface $articles
 * @var array<string, string>|null $strategy
 */
$prefix = $strategy['prefix'] ?? 'FullBackend';
?>
<?= $this->element('strategy_banner', ['strategy' => $strategy]) ?>
<h1>Articles</h1>
<p class="note">
    <?php if (($strategy['slug'] ?? null) === \App\Service\Strategy::ADAPTER_ONLY): ?>
        This list shows <strong>every</strong> article regardless of the
        active role — AdapterOnly deliberately does not apply entity-level
        scopes. Role-based gating (can you even reach this action?) still
        applies, but row filtering does not.
    <?php else: ?>
        This list has been narrowed by
        <code>$this->Authorization->applyScope($query, 'index')</code>,
        which dispatches to <code>TinyAuthPolicy::scopeIndex()</code>
        and applies the <code>own</code> scope for regular users. Switch
        roles via the Demo home page to see the result change.
    <?php endif; ?>
</p>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= $this->Html->link($article->title, ['action' => 'view', $article->id]) ?></td>
                <td><?= h($article->user->username ?? '?') ?></td>
                <td><?= h($article->status) ?></td>
                <td>
                    <?= $this->Html->link('View', ['action' => 'view', $article->id]) ?>
                    &middot;
                    <?= $this->Html->link('Edit', ['action' => 'edit', $article->id]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($articles)): ?>
            <tr><td colspan="4" class="empty">No articles visible under current role + strategy.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
