<?php
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
namespace Tokens\Test\TestCase\Model\Entity;

use Cake\I18n\Time;
use Cake\TestSuite\TestCase;
use Tokens\Model\Entity\Token;

/**
 * Tokens\Model\Entity\Token Test Case
 */
class TokenTest extends TestCase
{
    /**
     * Test for `_setExpiry()` method
     * @test
     */
    public function testExpirySetMutator()
    {
        $entity = new Token;

        $entity->set('expiry', '+1 day');
        $this->assertInstanceOf('Cake\I18n\Time', $entity->expiry);
        $this->assertTrue($entity->expiry->isTomorrow());

        $entity->set('expiry', new Time('+1 day'));
        $this->assertInstanceOf('Cake\I18n\Time', $entity->expiry);
        $this->assertTrue($entity->expiry->isTomorrow());

        foreach (['Date', 'FrozenDate', 'FrozenTime', 'Time'] as $class) {
            $class = 'Cake\I18n\\' . $class;
            $entity->set('expiry', new $class);
            $this->assertInstanceOf($class, $entity->expiry);
        }
    }

    /**
     * Test for `_setToken()` method
     * @test
     */
    public function testTokenSetMutator()
    {
        $regex = '/^[a-z0-9]{25}$/';

        $entity = new Token;

        $entity->set('token', null);
        $this->assertNull($entity->token);

        $entity->set('token', 'test');
        $this->assertRegExp($regex, $entity->token);

        $entity->set('token', ['first', 'second']);
        $this->assertRegExp($regex, $entity->token);

        $entity->set('token', (object)['first', 'second']);
        $this->assertRegExp($regex, $entity->token);
    }
}
