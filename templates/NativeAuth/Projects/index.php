<?php
/** @var \App\View\AppView $this */
/** @var array $projects */
/** @var array|null $strategy */
echo $this->element('projects_index', compact('projects', 'strategy'));
