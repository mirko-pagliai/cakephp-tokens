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
     * @param string $token Token value
     * @param int|null $user User ID
     * @param string|null $type Token type
     * @return \Cake\ORM\Query
     * @uses _getTable()
     */
    protected function _find($token, $user = null, $type = null)
    {
        $conditions = compact('token');

        if (!empty($user)) {
            $conditions['user_id'] = $user;
        }

        if (!empty($type)) {
            $conditions['type'] = $type;
        }

        return $this->_getTable()->find('active')->where($conditions);
    }

    /**
     * Checks if a token exists and is active
     * @param string $token Token value
     * @param int|null $user Optional user ID
     * @param string|null $type Optional token type
     * @return bool
     * @uses _find()
     */
    public function check($token, $user = null, $type = null)
    {
        return (bool)$this->_find($token, $user, $type)->count();
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
        $query = $this->_find($token);

        if (!$query->count()) {
            return false;
        }

        return $this->_getTable()->delete($query->first());
    }
}
