<?php
/**
 * This file is part of cakephp-tokens.
 *
 * cakephp-tokens is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * cakephp-tokens is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with cakephp-tokens.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace Tokens\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * Tokens Model
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
     */
    public function beforeSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options)
    {
        if (empty($entity->token)) {
            $entity->token = time();
        }

        $entity->token = substr(Security::hash($entity->token, 'sha1', true), 0, 25);

        if (empty($entity->expiry)) {
            $entity->expiry = '+2 hour';
        }

        $entity->expiry = new Time($entity->expiry);

        if (!empty($entity->extra)) {
            $entity->extra = serialize($entity->extra);
        }

        return true;
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
        $query->formatResults(function ($results) {
            return $results->map(function ($row) {
                if (!empty($row->extra)) {
                    $row->extra = unserialize($row->extra);
                }

                return $row;
            });
        });

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

        $this->table('tokens');
        $this->displayField('id');
        $this->primaryKey('id');
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
            ->allowEmpty('type');

        $validator
            ->requirePresence('token', 'create')
            ->notEmpty('token');

        $validator
            ->allowEmpty('data');

        $validator
            ->dateTime('expiry')
            ->allowEmpty('expiry');

        return $validator;
    }
}
