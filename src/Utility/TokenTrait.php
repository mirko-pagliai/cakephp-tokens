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
namespace Tokens\Utility;

use Cake\ORM\TableRegistry;
use Tokens\Model\Entity\Token;

/**
 * TokenTrait
 */
trait TokenTrait
{
    /**
     * Errors occurred during the creation of the last token
     * @var array
     */
    protected $errors = [];

    /**
     * Returns the table instance
     * @return \Tokens\Model\Table\TokensTable
     */
    protected function _getTable()
    {
        return TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);
    }

    /**
     * Internal `find()` method
     * @param array $conditions Conditions for `where()`
     * @return \Cake\ORM\Query
     * @uses _getTable()
     */
    protected function _find(array $conditions = [])
    {
        return $this->_getTable()->find('active')->where($conditions);
    }

    /**
     * Checks if a token exists and is active.
     *
     * Valid optios values: `user_id`, `type`.
     *
     * Be careful: if the token has these options, then you must set them.
     * In other words, blank options will be replaced with `null`.
     * @param string $token Token value
     * @param array $options Options
     * @return bool
     * @uses _find()
     */
    public function check($token, array $options = [])
    {
        $conditions = compact('token');

        foreach (['user_id', 'type'] as $key) {
            $conditions[$key] = empty($options[$key]) ? null : $options[$key];
        }

        return (bool)$this->_find($conditions)->count();
    }

    /**
     * Creates a token.
     *
     * Valid optios values: `user_id`, `type`, `extra`, `expiry`.
     * @param string $token Token value
     * @param array $options Options
     * @return string|false Token value, otherwise `false` on failure
     * @uses _getTable()
     * @uses $errors
     */
    public function create($token, array $options = [])
    {
        //Resets errors
        $this->errors = [];

        $entity = new Token(compact('token'));

        foreach (['user_id', 'type', 'extra', 'expiry'] as $key) {
            $entity->set($key, empty($options[$key]) ? null : $options[$key]);
        }

        if (!$this->_getTable()->save($entity)) {
            //If the token has not been saved, stores the returned errors.
            //Errors will be accessible via the `errors()` method
            $this->errors = $entity->errors();

            return false;
        }

        return $entity->token;
    }

    /**
     * Returns errors that occurred during the creation of the last token
     * @return array
     * @uses $errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Deletes a token
     * @param string $token Token value
     * @return bool
     * @uses _find()
     * @uses _getTable()
     */
    public function delete($token)
    {
        $query = $this->_find(compact('token'));

        if (!$query->count()) {
            return false;
        }

        return $this->_getTable()->delete($query->first());
    }
}
