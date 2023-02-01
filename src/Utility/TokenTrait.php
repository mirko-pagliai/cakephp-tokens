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
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use LogicException;
use Tokens\Model\Entity\Token;
use Tokens\Model\Table\TokensTable;

/**
 * TokenTrait.
 *
 * It allows to handle tokens.
 */
trait TokenTrait
{
    use LocatorAwareTrait;

    /**
     * Internal method to get a `TokensTable` instance
     * @return \Tokens\Model\Table\TokensTable
     */
    protected function getTokensTable(): TokensTable
    {
        /** @var \Tokens\Model\Table\TokensTable $Table */
        $Table = $this->getTableLocator()->get('Tokens.Tokens');

        return $Table;
    }

    /**
     * `find()` method
     * @param array $conditions Conditions for `where()`
     * @return \Cake\ORM\Query
     */
    public function find(array $conditions = []): Query
    {
        return $this->getTokensTable()->find('active')->where($conditions);
    }

    /**
     * Checks if a token exists and is active.
     *
     * Valid options values:
     *  - `user_id`;
     *  - `type`.
     *
     * Be careful: if the token has these options, then you must set them.
     * In other words, blank options will be replaced with `null`.
     * @param string $token Token value
     * @param array $options Options
     * @return bool
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
     * Valid options values:
     *  - `user_id`;
     *  - `type`;
     *  - `extra`;
     *  - `expiry`.
     * @param string $token Token value
     * @param array $options Options
     * @return string Token value
     * @throws \LogicException
     */
    public function create(string $token, array $options = []): string
    {
        $Token = new Token(compact('token'));

        foreach (['user_id', 'type', 'extra', 'expiry'] as $key) {
            if (isset($options[$key])) {
                $Token->set($key, $options[$key]);
            }
        }

        if (!$this->getTokensTable()->save($Token)) {
            $error = array_value_first_recursive($Token->getErrors());

            throw new LogicException(sprintf('Error for `%s` field: %s', array_key_first($Token->getErrors()), lcfirst($error)));
        }

        return $Token->get('token');
    }

    /**
     * Deletes a token
     * @param string $token Token value
     * @return bool
     */
    public function delete(string $token): bool
    {
        $query = $this->find(compact('token'));

        $first = $query->first();

        return $query->count() && $first instanceof EntityInterface && $this->getTokensTable()->delete($first);
    }
}
