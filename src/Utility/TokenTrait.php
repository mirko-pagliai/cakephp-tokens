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
namespace Tokens\Utility;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use LogicException;
use Tokens\Model\Entity\Token;

/**
 * TokenTrait.
 * It allows to handle tokens.
 */
trait TokenTrait
{
    /**
     * Internal method to get the table instance
     * @return \Cake\ORM\Table
     */
    protected function getTable(): Table
    {
        return TableRegistry::getTableLocator()->get('Tokens.Tokens');
    }

    /**
     * `find()` method
     * @param array $conditions Conditions for `where()`
     * @return \Cake\ORM\Query
     * @uses getTable()
     */
    public function find(array $conditions = []): Query
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
    public function check(string $token, array $options = []): bool
    {
        $conditions = compact('token');

        foreach (['user_id', 'type'] as $key) {
            if (!isset($options[$key])) {
                $key = sprintf('%s IS', $key);
            }
            $conditions[$key] = $options[$key] ?? null;
        }

        return !$this->find($conditions)->all()->isEmpty();
    }

    /**
     * Creates a token.
     *
     * Valid optios values: `user_id`, `type`, `extra`, `expiry`.
     * @param string $token Token value
     * @param array $options Options
     * @return string Token value
     * @throws \LogicException
     * @uses getTable()
     */
    public function create(string $token, array $options = []): string
    {
        $entity = new Token(compact('token'));

        foreach (['user_id', 'type', 'extra', 'expiry'] as $key) {
            if (array_key_exists($key, $options)) {
                $entity->set($key, $options[$key]);
            }
        }

        if (!$this->getTable()->save($entity)) {
            $field = array_key_first($entity->getErrors());
            $error = array_value_first_recursive($entity->getErrors());

            throw new LogicException(sprintf('Error for `%s` field: %s', $field, lcfirst($error)));
        }

        return $entity->get('token');
    }

    /**
     * Deletes a token
     * @param string $token Token value
     * @return bool
     * @uses find()
     * @uses getTable()
     */
    public function delete(string $token): bool
    {
        $query = $this->find(compact('token'));

        $first = $query->first();

        return $query->count() && $first instanceof EntityInterface ? $this->getTable()->delete($first) : false;
    }
}
