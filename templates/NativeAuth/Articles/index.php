<?php
/** @var \App\View\AppView $this */
/** @var array $articles */
/** @var array|null $strategy */
echo $this->element('articles_index', compact('articles', 'strategy'));
