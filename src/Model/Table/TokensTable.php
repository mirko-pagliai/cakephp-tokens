<?php
declare(strict_types=1);

/**
 * This file is part of cakephp-tokens.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/cakephp-thumber
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Tokens\Model\Table;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Tokens\Model\Entity\Token;

/**
 * Tokens Model
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @method \Tokens\Model\Entity\Token get($primaryKey, $options = [])
 * @method \Tokens\Model\Entity\Token newEntity($data = null, array $options = [])
 * @method \Tokens\Model\Entity\Token[] newEntities(array $data, array $options = [])
 * @method \Tokens\Model\Entity\Token|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Tokens\Model\Entity\Token patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Tokens\Model\Entity\Token[] patchEntities($entities, array $data, array $options = [])
 * @method \Tokens\Model\Entity\Token findOrCreate($search, callable $callback = null)
 */
class TokensTable extends Table
{
    /**
     * Called before each entity is saved.
     * Stopping this event will abort the save operation.
     * @param \Cake\Event\Event $event Event
     * @param \Cake\ORM\Entity $entity Entity
     * @return bool
     * @uses deleteExpired()
     */
    public function beforeSave(Event $event, Entity $entity): bool
    {
        if (!$entity->has('expiry')) {
            $entity->set('expiry', Configure::read('Tokens.expiryDefaultValue'));
        }

        if ($entity->has('extra')) {
            $entity->set('extra', serialize($entity->get('extra')));
        }

        //Deletes all expired tokens and tokens with the same token value
        //  and/or the same user.
        $this->deleteExpired($entity);

        return true;
    }

    /**
     * Deletes all expired tokens.
     *
     * If a `$token` entity is passed, it also clears tokens with the same
     *  token value and/or the same user.
     *
     * This method should be called before creating a new token. In fact, it
     *  prevents a user from having more than token or a token is created with
     *  the same token value.
     * @param \Tokens\Model\Entity\Token|null $entity Token entity
     * @return int Affected rows
     */
    public function deleteExpired(?Token $entity = null): int
    {
        $conditions = ['expiry <' => Time::now()];

        if ($entity && $entity->has('token')) {
            $conditions['token'] = $entity->get('token');
        }

        if ($entity && $entity->has('user_id')) {
            $conditions['user_id'] = $entity->get('user_id');
        }

        $conditions = count($conditions) > 1 ? ['OR' => $conditions] : $conditions;

        return $this->deleteAll($conditions);
    }

    /**
     * Basic `find()` method.
     *
     * This rewrites the method provided by CakePHP, to unserialize the `extra`
     *  field.
     * @param string $type Find type
     * @param array|\ArrayAccess $options An array that will be passed to
     *  Query::applyOptions()
     * @return \Cake\ORM\Query
     */
    public function find(string $type = 'all', $options = []): Query
    {
        $query = parent::find($type, $options);

        //Unserializes the `extra` field.
        return $query->formatResults(function (ResultSet $results) {
            return $results->map(function (Token $token) {
                if ($token->has('extra')) {
                    $token->set('extra', @unserialize($token->get('extra')));
                }

                return $token;
            });
        });
    }

    /**
     * `active` find method
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findActive(Query $query): Query
    {
        return $query->where(['expiry >=' => Time::now()]);
    }

    /**
     * `expired` find method
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findExpired(Query $query): Query
    {
        return $query->where(['expiry <' => Time::now()]);
    }

    /**
     * Initialize method
     * @param array $config Configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('tokens');
        $this->setDisplayField('token');
        $this->setPrimaryKey('id');

        $usersClass = $config['usersClassOptions'] ?? Configure::read('Tokens.usersClassOptions');
        if ($usersClass) {
            $this->belongsTo('Users', $usersClass);

            if (!$this->Users->hasAssociation('tokens')) {
                $this->Users->hasMany('Tokens')->setForeignKey('user_id');
            }
        }
    }

    /**
     * Build rules.
     *
     * It uses validation rules as application rules.
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        //Uses validation rules as application rules
        $rules->add(function (Token $entity) {
            $errors = $this->getValidator('default')->validate(
                $entity->extract($this->getSchema()->columns(), true),
                $entity->isNew()
            );
            $entity->setErrors($errors);

            return empty($errors);
        });

        return $rules->add($rules->existsIn(['user_id'], 'Users'));
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator->integer('id')->allowEmptyString('id')
            ->allowEmptyDateTime('create')
            ->requirePresence('token', 'create')->notEmptyString('token')
            ->lengthBetween('type', [3, 255])->allowEmptyString('type')
            ->allowEmptyString('extra')
            ->dateTime('expiry')->allowEmptyDateTime('expiry');
    }
}
