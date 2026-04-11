<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Project $project */
/** @var array|null $strategy */
echo $this->element('project_edit', compact('project', 'strategy'));
