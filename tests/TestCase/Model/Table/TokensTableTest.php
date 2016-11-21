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
     * @var \Tokens\Model\Table\TokensTable
     */
    protected $Tokens;

    /**
     * @var \Cake\ORM\Association\BelongsTo
     */
    protected $Users;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'core.users',
        'plugin.tokens.tokens',
    ];

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tokens = TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);
        $this->Users = $this->Tokens->Users;
    }

    /**
     * tearDown method
     * @return void
     */
    public function tearDown()
    {
        unset($this->Users, $this->Tokens);

        parent::tearDown();
    }

    /**
     * Test for `Users` association
     * @test
     */
    public function testAssociationWithUsers()
    {
        //Token with ID 1 has no user
        $token = $this->Tokens->findById(1)->contain('Users')->first();
        $this->assertEmpty($token->user);

        //Token with ID 3 has user with ID 2
        $token = $this->Tokens->findById(3)->contain('Users')->first();
        $this->assertEquals('Cake\ORM\Entity', get_class($token->user));
        $this->assertEquals(1, $token->user->id);

        //User with ID 2 has tokens with ID 3 and 4
        $tokens = $this->Users->findById(2)->contain('Tokens')->extract('tokens')->first();
        $this->assertEquals(2, count($tokens));
        $this->assertEquals(2, $tokens[0]->id);
        $this->assertEquals(4, $tokens[1]->id);

        //Token with ID 3 matches with the user with ID
        $token = $this->Tokens->find()->matching('Users', function ($q) {
            return $q->where(['Users.id' => 1]);
        })->extract('id')->toArray();
        $this->assertEquals([3], $token);
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $token = $this->Tokens->save(new Token);

        $this->assertNotEmpty($token);
        $this->assertEquals('Tokens\Model\Entity\Token', get_class($token));
        $this->assertEquals(null, $token->user_id);
        $this->assertRegExp('/^[a-z0-9]{25}$/', $token->token);
        $this->assertEmpty($token->type);
        $this->assertEquals('Cake\I18n\FrozenTime', get_class($token->expiry));
        $this->assertEmpty($token->extra);

        $token = $this->Tokens->save(new Token(['expiry' => '+1 day']));

        $this->assertNotEmpty($token);
        $this->assertTrue($token->expiry->isTomorrow());
        $this->assertEquals('Cake\I18n\FrozenTime', get_class($token->expiry));

        $token = $this->Tokens->save(new Token(['extra' => 'testExtra']));

        $this->assertNotEmpty($token);
        $this->assertEquals('s:9:"testExtra";', $token->extra);

        $token = $this->Tokens->save(new Token(['extra' => ['first', 'second']]));

        $this->assertNotEmpty($token);
        $this->assertEquals('a:2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);

        $token = $this->Tokens->save(new Token(['extra' => (object)['first', 'second']]));

        $this->assertNotEmpty($token);
        $this->assertEquals('O:8:"stdClass":2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);
    }

    /**
     * Test for `deleteExpired()` method
     * @test
     */
    public function testDeleteExpired()
    {
        $count = $this->Tokens->deleteExpired();
        $this->assertEquals(1, $count);

        //Token with ID 2 does not exist anymore
        $this->assertEmpty($this->Tokens->findById(2)->first());

        $this->loadFixtures('Tokens');

        //Same as tokens with ID 2 and 4
        $token = new Token(['user_id' => 2]);

        $count = $this->Tokens->deleteExpired($token);
        $this->assertEquals(2, $count);

        //Tokens with ID 2 and 4 do not exist anymore
        $this->assertEmpty($this->Tokens->find()->where(['id' => 2])->orWhere(['id' => 4])->toArray());

        $this->loadFixtures('Tokens');

        //Same as token with ID 3
        $token = new Token(['token' => 'token3']);

        $count = $this->Tokens->deleteExpired($token);
        $this->assertEquals(2, $count);

        //Tokens with ID 2 and 3 do not exist anymore
        $this->assertEmpty($this->Tokens->find()->where(['id' => 2])->orWhere(['id' => 3])->toArray());
    }

    /**
     * Test for `find()` method. It tests that `extra` is formatted
     * @test
     */
    public function testFindFormatsExtraFields()
    {
        $query = $this->Tokens->find();
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        $tokens = $query->extract('extra')->toArray();

        $this->assertEquals(null, $tokens[0]);
        $this->assertEquals('testExtra', $tokens[1]);
        $this->assertEquals(['first', 'second'], $tokens[2]);
        $this->assertEquals((object)['first', 'second'], $tokens[3]);
    }

    /**
     * Test for `active` `find()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Tokens->find('active');
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        $tokens = $query->extract('id')->toArray();

        //Results are tokens with ID 1, 3 and 4
        $this->assertEquals([1, 3, 4], $tokens);
    }

    /**
     * Test for `expired` `find()` method
     * @test
     */
    public function testFindExpired()
    {
        $query = $this->Tokens->find('expired');
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        $tokens = $query->extract('id')->toArray();

        //Results is token with ID 2
        $this->assertEquals([2], $tokens);
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
        //Only `token` is required.
        //`expiry` is also required, but it will be added by `beforeSave()`
        $token = $this->Tokens->newEntity(['token' => 'test']);
        $this->assertEmpty($token->errors());
    }

    /**
     * Test validation for `expiry` property
     * @test
     */
    public function testValidationForExpiry()
    {
        //Valid `expiry` values
        foreach ([
            '\Cake\I18n\Date',
            '\Cake\I18n\Time',
            '\Cake\I18n\FrozenDate',
            '\Cake\I18n\FrozenTime',
        ] as $class) {
            $token = $this->Tokens->newEntity([
                'token' => 'test',
                'expiry' => new $class,
            ]);
            $this->assertEmpty($token->errors());
        }

        //Invalid `expiry` value
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
        //`token` value is required
        $token = $this->Tokens->newEntity([]);
        $this->assertEquals(['token' => ['_required' => 'This field is required']], $token->errors());

        //Valid `token` value
        $token = new Token(['token' => 'uniqueValue']);
        $this->assertNotEmpty($this->Tokens->save($token));
        $this->assertEmpty($token->errors());

        //Invalid `token` value
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
        //Valid `type` value
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => '123',
        ]);
        $this->assertEmpty($token->errors());

        //Valid `type` value
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => str_repeat('a', 255),
        ]);
        $this->assertEmpty($token->errors());

        //Invalid `type` value (it is too short)
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => '12',
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->errors());

        //Invalid `type` value (it is too long)
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => str_repeat('a', 256),
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->errors());
    }

    /**
     * Test validation for `user_id` property
     * @test
     */
    public function testValidationForUserId()
    {
        //Valid `user_id` value
        $token = $this->Tokens->newEntity([
            'user_id' => '2',
            'token' => 'firstToken',
        ]);
        $this->assertNotEmpty($this->Tokens->save($token));
        $this->assertEmpty($token->errors());

        //Invalid `user_id` value (the user does not exist)
        $token = $this->Tokens->newEntity([
            'user_id' => '999',
            'token' => 'secondToken',
        ]);
        $this->assertFalse($this->Tokens->save($token));
        $this->assertEquals(['user_id' => ['_existsIn' => 'This value does not exist']], $token->errors());
    }
}
