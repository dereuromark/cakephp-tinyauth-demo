<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Article Entity
 *
 * Demonstrates TinyAuth resource-level permissions with "own" scope.
 * - user_id: Used by "own" scope to check if user owns the article
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $body
 * @property string $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Article extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'title' => true,
        'body' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
