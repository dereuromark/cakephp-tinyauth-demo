<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Article $article */
/** @var array|null $strategy */
echo $this->element('article_edit', compact('article', 'strategy'));
