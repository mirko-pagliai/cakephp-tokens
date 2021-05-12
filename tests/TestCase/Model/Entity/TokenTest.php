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
namespace Tokens\Test\TestCase\Model\Entity;

use Cake\I18n\Date;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use MeTools\TestSuite\TestCase;
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
        $entity = new Token();

        foreach (['+1 day', new Time('+1 day')] as $expiry) {
            $entity->set('expiry', $expiry);
            $this->assertInstanceOf(Time::class, $entity->get('expiry'));
            $this->assertTrue($entity->get('expiry')->isTomorrow());
        }

        foreach ([Date::class, FrozenDate::class, FrozenTime::class, Time::class] as $class) {
            $entity->set('expiry', new $class());
            $this->assertInstanceOf($class, $entity->get('expiry'));
        }
    }

    /**
     * Test for `_setToken()` method
     * @test
     */
    public function testTokenSetMutator()
    {
        $entity = new Token();
        $entity->set('token', null);
        $this->assertNull($entity->get('token'));

        foreach ([
            'test',
            ['first', 'second'],
            (object)['first', 'second'],
        ] as $token) {
            $entity->set('token', $token);
            $this->assertMatchesRegularExpression('/^[\d\w]{25}$/', $entity->get('token'));
        }
    }
}
