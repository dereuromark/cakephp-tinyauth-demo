<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Team Entity
 *
 * Supporting entity for team-based scope demonstrations.
 *
 * @property int $id
 * @property string $name
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property array<\App\Model\Entity\User> $users
 * @property array<\App\Model\Entity\Project> $projects
 */
class Team extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'created' => true,
        'modified' => true,
        'users' => true,
        'projects' => true,
    ];
}
