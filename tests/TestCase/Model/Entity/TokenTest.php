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
namespace Tokens\Test\TestCase\Model\Entity;

use Cake\Core\Configure;
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
        $entity = new Token();

        $entity->set('expiry', '+1 day');
        $this->assertEquals('Cake\I18n\Time', get_class($entity->expiry));
        $this->assertTrue($entity->expiry->isTomorrow());

        $entity->set('expiry', new \Cake\I18n\Time);
        $this->assertEquals('Cake\I18n\Time', get_class($entity->expiry));

        $entity->set('expiry', new \Cake\I18n\Time('+1 day'));
        $this->assertEquals('Cake\I18n\Time', get_class($entity->expiry));
        $this->assertTrue($entity->expiry->isTomorrow());

        $entity->set('expiry', new \Cake\I18n\FrozenTime);
        $this->assertEquals('Cake\I18n\FrozenTime', get_class($entity->expiry));

        $entity->set('expiry', new \Cake\I18n\Date);
        $this->assertEquals('Cake\I18n\Date', get_class($entity->expiry));

        $entity->set('expiry', new \Cake\I18n\FrozenDate);
        $this->assertEquals('Cake\I18n\FrozenDate', get_class($entity->expiry));
    }

    /**
     * Test for `_setToken()` method
     * @test
     */
    public function testTokenSetMutator()
    {
        $regex = '/^[a-z0-9]{25}$/';

        $entity = new Token();

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
