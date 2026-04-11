<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Teams Model
 *
 * Supporting model for team-based scope demonstrations.
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ProjectsTable> $Projects
 * @method \App\Model\Entity\Team newEmptyEntity()
 * @method \App\Model\Entity\Team newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Team> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Team get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Team findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Team>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Team patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Team> patchEntities(iterable<\App\Model\Entity\Team> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Team|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Team saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Team> find(string $type = 'all', mixed ...$args)
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Team>|false saveMany(iterable<\App\Model\Entity\Team> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Team> saveManyOrFail(iterable<\App\Model\Entity\Team> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Team>|false deleteMany(iterable<\App\Model\Entity\Team> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Team> deleteManyOrFail(iterable<\App\Model\Entity\Team> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class TeamsTable extends Table
{
    /**
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('teams');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'team_id',
        ]);

        $this->hasMany('Projects', [
            'foreignKey' => 'team_id',
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator
     *
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        return $validator;
    }
}
