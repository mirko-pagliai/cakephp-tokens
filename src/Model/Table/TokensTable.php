<?php
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

use ArrayObject;
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
     * @param \ArrayObject $options Options
     * @return bool
     * @uses deleteExpired()
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if (empty($entity->expiry)) {
            $entity->expiry = Configure::read('Tokens.expiryDefaultValue');
        }

        if (!empty($entity->extra)) {
            $entity->extra = serialize($entity->extra);
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
    public function deleteExpired(Token $entity = null)
    {
        $conditions[] = ['expiry <' => new Time()];

        if (!empty($entity->token)) {
            $conditions[] = ['token' => $entity->token];
        }

        if (!empty($entity->user_id)) {
            $conditions[] = ['user_id' => $entity->user_id];
        }

        return $this->deleteAll(['OR' => $conditions]);
    }

    /**
     * Basic `find()` method.
     *
     * This rewrites the method provided by CakePHP, to unserialize the `extra`
     *  field.
     * @param string $type Find type
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query
     */
    public function find($type = 'all', $options = [])
    {
        $query = parent::find($type, $options);

        //Unserializes the `extra` field.
        $query->formatResults(function (ResultSet $results) {
            return $results->map(function (Token $token) {
                if ($token->extra) {
                    $token->extra = safe_unserialize($token->extra);
                }

                return $token;
            });
        });

        return $query;
    }

    /**
     * `active` find method
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findActive(Query $query)
    {
        $query->where(['expiry >=' => new Time]);

        return $query;
    }

    /**
     * `expired` find method
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findExpired(Query $query)
    {
        $query->where(['expiry <' => new Time]);

        return $query;
    }

    /**
     * Initialize method
     * @param array $config Configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('tokens');
        $this->setDisplayField('token');
        $this->setPrimaryKey('id');

        if (Configure::read('Tokens.usersClassOptions')) {
            $this->belongsTo('Users', Configure::read('Tokens.usersClassOptions'));

            if (!$this->Users->hasAssociation('tokens')) {
                $this->Users->hasMany('Tokens')->setForeignKey('user_id');
            }
        }
    }

    /**
     * Build rules.
     *
     * It uses validation rules as application rules.
     * @param Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        //Uses validation rules as application rules
        $rules->add(function (Token $entity) {
            $errors = $this->getValidator('default')->errors(
                $entity->extract($this->getSchema()->columns(), true),
                $entity->isNew()
            );
            $entity->setErrors($errors);

            return empty($errors);
        });

        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('token', 'create')
            ->notEmpty('token');

        $validator
            ->lengthBetween('type', [3, 255])
            ->allowEmpty('type');

        $validator
            ->allowEmpty('extra');

        $validator
            ->dateTime('expiry')
            ->allowEmpty('expiry');

        return $validator;
    }
}
