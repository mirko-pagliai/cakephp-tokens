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

use Cake\Core\Configure;
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
    public $fixtures = [
        'core.users',
        'plugin.tokens.tokens',
    ];

    /**
     * Internal method to create some tokens
     * @return array
     */
    protected function _createSomeTokens()
    {
        //Deletes all tokens
        $this->Tokens->deleteAll([]);

        //Create three tokens. The second is expired
        return [
            $this->Tokens->save(new Token(['id' => 1, 'user_id' => 1, 'token' => 'token1', 'expiry' => '+1 day'])),
            $this->Tokens->save(new Token(['id' => 2, 'user_id' => 2, 'token' => 'token2', 'expiry' => '-1 day'])),
            $this->Tokens->save(new Token(['id' => 3, 'user_id' => 3, 'token' => 'token3', 'expiry' => '+2 day'])),
        ];
    }

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tokens = TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);
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
        $this->assertNull($token->user_id);
        $this->assertRegExp('/^[a-z0-9]{25}$/', $token->token);
        $this->assertEmpty($token->type);
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
            'token' => 'token3',
            'type' => 'testType',
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
     * Test for `Users` association
     * @test
     */
    public function testBelongsToUsers()
    {
        //Token with ID 1 has no user
        $token = $this->Tokens->findById(1)->contain('Users')->first();
        $this->assertEmpty($token->user);

        //Token with ID 3 has user with ID 2
        $token = $this->Tokens->findById(3)->contain('Users')->first();
        $this->assertEquals('Cake\ORM\Entity', get_class($token->user));
        $this->assertEquals(2, $token->user->id);

        //User with ID 2 has tokens with ID 3 and 4
        $user = $this->Tokens->Users->findById(2)->contain('Tokens')->first();
        $this->assertEquals(3, $user->tokens[0]->id);
        $this->assertEquals(4, $user->tokens[1]->id);
    }

    /**
     * Test for `deleteExpired()` method
     * @test
     * @uses _createSomeTokens()
     */
    public function testDeleteExpired()
    {
        //Create some tokens
        $this->_createSomeTokens();

        $count = $this->Tokens->deleteExpired();
        $this->assertEquals(1, $count);

        //Token with ID 2 does not exist anymore
        $this->assertEmpty($this->Tokens->find()->where(['id' => 2])->first());

        //Create some tokens
        $this->_createSomeTokens();

        $token = new Token(['user_id' => 1]);
        $count = $this->Tokens->deleteExpired($token);
        $this->assertEquals(2, $count);

        //Tokens with ID 1 and 2 do not exist anymore
        $this->assertEmpty($this->Tokens->find()->where(['id' => 1])->first());
        $this->assertEmpty($this->Tokens->find()->where(['id' => 2])->first());

        //Create some tokens
        $this->_createSomeTokens();

        $token = new Token(['token' => 'token3']);
        $count = $this->Tokens->deleteExpired($token);
        $this->assertEquals(2, $count);

        //Tokens with ID 2 and 3 do not exist anymore
        $this->assertEmpty($this->Tokens->find()->where(['id' => 2])->first());
        $this->assertEmpty($this->Tokens->find()->where(['id' => 3])->first());
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
        $query = $this->Tokens->find('active');
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEmpty($query->hydrate(false)->toArray());

        //Create some tokens
        $this->_createSomeTokens();

        $result = $this->Tokens->find('active')->hydrate(false)->toArray();
        $this->assertEquals(2, count($result));

        //Results are tokens with ID 1 and 3
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(3, $result[1]['id']);
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

        //Create some tokens
        $this->_createSomeTokens();

        $result = $this->Tokens->find('expired')->hydrate(false)->toArray();
        $this->assertEquals(1, count($result));

        //The first result is the token with ID 2
        $this->assertEquals(2, $result[0]['id']);
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

        $this->assertNotEmpty($this->Tokens->association('users'));
        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->Tokens->Users));
        $this->assertEquals('users', $this->Tokens->Users->table());
    }

    /**
     * Test for a custum `Users` table
     * @test
     */
    public function testForCustomUsersTable()
    {
        Configure::write('Tokens.usersClassOptions.className', 'TestApp.Users');

        TableRegistry::clear();
        $this->Tokens = TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);

        $this->assertEquals('TestApp.Users', $this->Tokens->Users->className());
        $this->assertEquals('This is a test method', $this->Tokens->Users->test());

        $token = $this->Tokens->findById(2)->contain('Users')->first();
        $this->assertEquals('TestApp\Model\Entity\User', get_class($token->user));
        $this->assertEquals('This is a test property', $token->user->test);
    }

    /**
     * Test for a no `Users` table
     * @expectedException RuntimeException
     * @expectedExceptionMessage Table "Tokens\Model\Table\TokensTable" is not associated with "Users"
     * @test
     */
    public function testForNoUsersTable()
    {
        Configure::write('Tokens.usersClassOptions', false);

        TableRegistry::clear();
        $this->Tokens = TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);

        $this->Tokens->Users;
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
