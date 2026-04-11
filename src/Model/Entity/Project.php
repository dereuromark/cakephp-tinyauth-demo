<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Project Entity
 *
 * Demonstrates TinyAuth resource-level permissions with both "own" and "team" scopes.
 * - user_id: Used by "own" scope to check if user owns the project
 * - team_id: Used by "team" scope to check if user is on the same team
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $team_id
 * @property string $name
 * @property string|null $description
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property bool $canEdit
 * @property bool $canDelete
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Team|null $team
 */
class Project extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'team_id' => true,
        'name' => true,
        'description' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'team' => true,
    ];
}
