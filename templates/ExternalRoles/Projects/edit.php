<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $project
 * @var mixed $strategy
 */
/** @var \App\Model\Entity\Project $project */
/** @var array|null $strategy */
echo $this->element('project_edit', compact('project', 'strategy'));
