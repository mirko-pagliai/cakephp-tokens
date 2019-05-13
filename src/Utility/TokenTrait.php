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
     * @return \Tokens\Model\Table\TokensTable
     */
    protected function getTable()
    {
        return TableRegistry::get('Tokens.Tokens');
    }

    /**
     * `find()` method
     * @param array $conditions Conditions for `where()`
     * @return \Cake\ORM\Query
     * @uses getTable()
     */
    public function find(array $conditions = [])
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
            $conditions[$key] = array_key_exists($key, $options) ? $options[$key] : null;
        }

        return !$this->find($conditions)->isEmpty();
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
    public function create($token, array $options = [])
    {
        $entity = new Token(compact('token'));

        foreach (['user_id', 'type', 'extra', 'expiry'] as $key) {
            if (array_key_exists($key, $options)) {
                $entity->set($key, $options[$key]);
            }
        }

        if (!$this->getTable()->save($entity)) {
            $field = collection(array_keys($entity->getErrors()))->first();
            $error = collection(collection($entity->getErrors())->first())->first();

            throw new LogicException(sprintf('Error for `%s` field: %s', $field, lcfirst($error)));
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

        return $query->count() ? $this->getTable()->delete($query->first()) : false;
    }
}
