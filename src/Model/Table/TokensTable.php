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

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
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
     * @param \Cake\Validation\Validator $validator
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
