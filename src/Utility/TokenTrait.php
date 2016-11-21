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
     * @param string $token Token value
     * @param array $options Options
     * @return bool
     * @uses _find()
     */
    public function check($token, array $options = [])
    {
        $data = compact('token');

        foreach (['user_id', 'type'] as $key) {
            if (!empty($options[$key])) {
                $data[$key] = $options[$key];
            }
        }

        return (bool)$this->_find($data)->count();
    }

    /**
     * Creates a token.
     *
     * Valid optios values: `user_id`, `type`, `extra`, `expiry`.
     * @param string $token Token value
     * @param array $options Options
     * @return string|false Token value, otherwise `false` on failure
     * @uses _getTable()
     */
    public function create($token, array $options = [])
    {
        $entity = new Token(compact('token'));

        foreach (['user_id', 'type', 'extra', 'expiry'] as $key) {
            if (!empty($options[$key])) {
                $entity->set($key, $options[$key]);
            }
        }

        if (!$this->_getTable()->save($entity)) {
            return false;
        }

        return $entity->token;
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
