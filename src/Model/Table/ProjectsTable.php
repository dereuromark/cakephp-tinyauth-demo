<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Projects Model
 *
 * Demonstrates TinyAuth resource-level permissions with both "own" and "team" scopes.
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\TeamsTable> $Teams
 * @method \App\Model\Entity\Project newEmptyEntity()
 * @method \App\Model\Entity\Project newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Project> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Project get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Project findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Project>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Project patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Project> patchEntities(iterable<\App\Model\Entity\Project> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Project|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Project saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Project> find(string $type = 'all', mixed ...$args)
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Project>|false saveMany(iterable<\App\Model\Entity\Project> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Project> saveManyOrFail(iterable<\App\Model\Entity\Project> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Project>|false deleteMany(iterable<\App\Model\Entity\Project> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Project> deleteManyOrFail(iterable<\App\Model\Entity\Project> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class ProjectsTable extends Table
{
    /**
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('projects');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Teams', [
            'foreignKey' => 'team_id',
            'joinType' => 'LEFT',
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
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->integer('team_id')
            ->allowEmptyString('team_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules
     *
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['team_id'], 'Teams'), ['errorField' => 'team_id']);

        return $rules;
    }
}
