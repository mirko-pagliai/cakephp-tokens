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

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Tokens\Model\Entity\Token;

/**
 * TokenTrait.
 * It allows to handle tokens.
 */
trait TokenTrait
{
    /**
     * Returns the table instance
     * @return \Tokens\Model\Table\TokensTable
     */
    protected function getTable()
    {
        return TableRegistry::get('Tokens.Tokens');
    }

    /**
     * Internal `find()` method
     * @param array $conditions Conditions for `where()`
     * @return \Cake\ORM\Query
     * @uses getTable()
     */
    protected function find(array $conditions = [])
    {
        return $this->getTable()->find('active')->where($conditions);
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
     * @uses find()
     */
    public function check($token, array $options = [])
    {
        $conditions = compact('token');

        foreach (['user_id', 'type'] as $key) {
            $conditions[$key] = empty($options[$key]) ? null : $options[$key];
        }

        return (bool)$this->find($conditions)->count();
    }

    /**
     * Creates a token.
     *
     * Valid optios values: `user_id`, `type`, `extra`, `expiry`.
     * @param string $token Token value
     * @param array $options Options
     * @return string Token value
     * @throws InternalErrorException
     * @uses getTable()
     */
    public function create($token, array $options = [])
    {
        $entity = new Token(compact('token'));

        foreach (['user_id', 'type', 'extra', 'expiry'] as $key) {
            if (!empty($options[$key])) {
                $entity->set($key, $options[$key]);
            }
        }

        if (!$this->getTable()->save($entity)) {
            $field = collection(array_keys($entity->getErrors()))->first();
            $error = collection(collection(($entity->getErrors()))->first())->first();

            throw new InternalErrorException(sprintf('Error for `%s` field: %s', $field, lcfirst($error)));
        }

        return $entity->token;
    }

    /**
     * Deletes a token
     * @param string $token Token value
     * @return bool
     * @uses find()
     * @uses getTable()
     */
    public function delete($token)
    {
        $query = $this->find(compact('token'));

        if (!$query->count()) {
            return false;
        }

        return $this->getTable()->delete($query->first());
    }
}
