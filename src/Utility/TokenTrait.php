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
     * Gets a table instance
     * @return \Tokens\Model\Table\TokensTable
     */
    protected function _getTable()
    {
        return TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);
    }

    /**
     * Deletes a token
     * @param string $token Token value
     * @return bool `true` if the token has been deleted
     * @uses _getTable()
     */
    public function delete($token)
    {
        $entity = $this->_getTable()->findByToken($token)->first();

        if (empty($entity)) {
            return false;
        }

        return $this->_getTable()->delete($entity);
    }
}
