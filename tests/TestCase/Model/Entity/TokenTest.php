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

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use MeTools\TestSuite\TestCase;
use Tokens\Model\Entity\Token;

/**
 * Tokens\Model\Entity\Token Test Case
 */
class TokenTest extends TestCase
{
    /**
     * @test
     * @uses \Tokens\Model\Entity\Token::_setExpiry()
     */
    public function testExpirySetMutator(): void
    {
        $Token = new Token();

        foreach (['+1 day', new FrozenTime('+1 day')] as $expiry) {
            $Token->set('expiry', $expiry);
            $this->assertInstanceOf(FrozenTime::class, $Token->get('expiry'));
            $this->assertTrue($Token->get('expiry')->isTomorrow());
        }

        foreach ([FrozenDate::class, FrozenTime::class] as $class) {
            $Token->set('expiry', new $class());
            $this->assertInstanceOf($class, $Token->get('expiry'));
        }
    }

    /**
     * @test
     * @uses \Tokens\Model\Entity\Token::_setToken()
     */
    public function testTokenSetMutator(): void
    {
        $Token = new Token();
        $Token->set('token', null);
        $this->assertEmpty($Token->get('token'));

        foreach ([
            'test',
            ['first', 'second'],
            (object)['first', 'second'],
        ] as $value) {
            $Token->set('token', $value);
            /** @var string $token */
            $token = $Token->get('token');
            $this->assertMatchesRegularExpression('/^[\d\w]{25}$/', $token);
        }
    }
}
