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
namespace Tokens\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Tokens\Model\Entity\Token;

/**
 * Tokens\Model\Table\TokensTable Test Case
 */
class TokensTableTest extends TestCase
{
    /**
     * Test subject
     * @var \Tokens\Model\Table\TokensTable
     */
    public $Tokens;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = ['plugin.tokens.tokens'];

    /**
     * Internal method to create some tokens
     * @return array
     */
    protected function _createSomeTokens()
    {
        //Create three tokens. The second is expired
        $first = $this->Tokens->save(new Token(['token' => 'token1', 'expiry' => '+1 day']));
        $second = $this->Tokens->save(new Token(['token' => 'token2', 'expiry' => '-1 day']));
        $third = $this->Tokens->save(new Token(['token' => 'token3', 'expiry' => '+2 day']));

        return [$first, $second, $third];
    }

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tokens = TableRegistry::get('Tokens.Tokens');
    }

    /**
     * tearDown method
     * @return void
     */
    public function tearDown()
    {
        unset($this->Tokens);

        parent::tearDown();
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $token = $this->Tokens->save(new Token(['token' => 'token1']));

        $this->assertNotEmpty($token);
        $this->assertEquals('Tokens\Model\Entity\Token', get_class($token));
        $this->assertEmpty($token->type);
        $this->assertRegExp('/^[a-z0-9]{25}$/', $token->token);
        $this->assertEquals('Cake\I18n\FrozenTime', get_class($token->expiry));
        $this->assertEmpty($token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'token2',
            'expiry' => '+1 day',
        ]));

        $this->assertNotEmpty($token);
        $this->assertTrue($token->expiry->isTomorrow());
        $this->assertEquals('Cake\I18n\FrozenTime', get_class($token->expiry));

        $token = $this->Tokens->save(new Token([
            'type' => 'testType',
            'token' => 'token3',
            'extra' => 'testExtra',
        ]));

        $this->assertNotEmpty($token);
        $this->assertEquals('testType', $token->type);
        $this->assertEquals('s:9:"testExtra";', $token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'token4',
            'extra' => ['first', 'second'],
        ]));

        $this->assertNotEmpty($token);
        $this->assertEquals('a:2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'token5',
            'extra' => (object)['first', 'second'],
        ]));

        $this->assertNotEmpty($token);
        $this->assertEquals('O:8:"stdClass":2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);
    }

    /**
     * Test for `deleteExpired()` method
     * @test
     * @uses _createSomeTokens()
     */
    public function testDeleteExpired()
    {
        //Deletes all tokens
        $this->Tokens->deleteAll([]);

        //Create three tokens. The second is expired
        list(, $second, ) = $this->_createSomeTokens();

        //The second token exists
        $token = $this->Tokens->find()->where(['id' => $second->id])->first();
        $this->assertNotEmpty($token);

        $count = $this->Tokens->deleteExpired();
        $this->assertEquals(1, $count);

        //The second token does not exist anymore
        $token = $this->Tokens->find()->where(['id' => $second->id])->first();
        $this->assertEmpty($token);
    }

    /**
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $token = $this->Tokens->get(1);

        $this->assertNotEmpty($token);
        $this->assertNull($token->extra);

        $token = $this->Tokens->get(2);

        $this->assertNotEmpty($token);
        $this->assertEquals('testExtra', $token->extra);

        $token = $this->Tokens->get(3);

        $this->assertNotEmpty($token);
        $this->assertEquals(['first', 'second'], $token->extra);

        $token = $this->Tokens->get(4);

        $this->assertNotEmpty($token);
        $this->assertEquals((object)['first', 'second'], $token->extra);
    }

    /**
     * Test for `active` `find()` method
     * @test
     * @uses _createSomeTokens()
     */
    public function testFindActive()
    {
        //Deletes all tokens
        $this->Tokens->deleteAll([]);

        $query = $this->Tokens->find('active');
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEmpty($query->hydrate(false)->toArray());

        //Create three tokens. The second is expired
        list($first,, $third) = $this->_createSomeTokens();

        $result = $this->Tokens->find('active')->hydrate(false)->toArray();
        $this->assertNotEmpty($result);
        $this->assertEquals(2, count($result));

        //The first result is the same as the first token
        $this->assertEquals($result[0]['token'], $first->token);

        //The second result is the same as the third token
        $this->assertEquals($result[1]['token'], $third->token);
    }

    /**
     * Test for `expired` `find()` method
     * @test
     * @uses _createSomeTokens()
     */
    public function testFindExpired()
    {
        //Deletes all tokens
        $this->Tokens->deleteAll([]);

        $query = $this->Tokens->find('expired');
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEmpty($query->hydrate(false)->toArray());

        //Create three tokens. The second is expired
        list(, $second, ) = $this->_createSomeTokens();

        $result = $this->Tokens->find('expired')->hydrate(false)->toArray();
        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result));

        //The first result is the same as the second token
        $this->assertEquals($result[0]['token'], $second->token);
    }

    /**
     * Test initialize method
     * @return void
     */
    public function testInitialize()
    {
        $this->assertEquals('Tokens\Model\Table\TokensTable', get_class($this->Tokens));
        $this->assertEquals('id', $this->Tokens->primaryKey());
        $this->assertEquals('token', $this->Tokens->displayField());
        $this->assertEquals('tokens', $this->Tokens->table());
    }

    /**
     * Test validation. Generic method
     * @test
     */
    public function testValidation()
    {
        $token = $this->Tokens->newEntity(['token' => 'test']);
        $this->assertEmpty($token->errors());
    }

    /**
     * Test validation for `expiry` property
     * @test
     */
    public function testValidationForExpiry()
    {
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'expiry' => new \Cake\I18n\Date,
        ]);
        $this->assertEmpty($token->errors());

        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'expiry' => new \Cake\I18n\Time,
        ]);
        $this->assertEmpty($token->errors());

        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'expiry' => new \Cake\I18n\FrozenDate,
        ]);
        $this->assertEmpty($token->errors());

        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'expiry' => new \Cake\I18n\FrozenTime,
        ]);
        $this->assertEmpty($token->errors());

        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'expiry' => 'thisIsAString',
        ]);
        $this->assertEquals(['expiry' => ['dateTime' => 'The provided value is invalid']], $token->errors());
    }

    /**
     * Test validation for `token` property
     * @test
     */
    public function testValidationForToken()
    {
        $token = $this->Tokens->newEntity([]);
        $this->assertEquals(['token' => ['_required' => 'This field is required']], $token->errors());

        $this->assertNotEmpty($this->Tokens->save(new Token(['token' => 'uniqueValue'])));

        $token = new Token(['token' => 'uniqueValue']);
        $this->assertFalse($this->Tokens->save($token));
        $this->assertEquals(['token' => ['_isUnique' => 'This value is already in use']], $token->errors());
    }

    /**
     * Test validation for `type` property
     * @test
     */
    public function testValidationForType()
    {
        $token = $this->Tokens->newEntity([
            'type' => '12',
            'token' => 'test',
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->errors());

        $token = $this->Tokens->newEntity([
            'type' => '123',
            'token' => 'test',
        ]);
        $this->assertEmpty($token->errors());

        $token = $this->Tokens->newEntity([
            'type' => str_repeat('a', 256),
            'token' => 'test',
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->errors());

        $token = $this->Tokens->newEntity([
            'type' => str_repeat('a', 255),
            'token' => 'test',
        ]);
        $this->assertEmpty($token->errors());
    }
}
