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
namespace Tokens\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\Http\BaseApplication;
use Cake\ORM\Query;
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
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'core.Users',
        'plugin.Tokens.Tokens',
    ];

    /**
     * Internal method to get the `Tokens.Tokens` table
     * @return \Tokens\Model\Table\TokensTable
     */
    protected function getTable()
    {
        return TableRegistry::get('Tokens.Tokens');
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);
        $app->addPlugin('Tokens')->pluginBootstrap();

        $this->Tokens = $this->getTable();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        $this->Tokens->deleteAll(['id >=' => 1]);

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
        $this->assertInstanceOf('Cake\ORM\Entity', $token->user);
        $this->assertEquals(1, $token->user->id);

        //User with ID 2 has tokens with ID 3 and 4
        $tokens = $this->Tokens->Users->findById(2)->contain('Tokens')->extract('tokens')->first();
        $this->assertEquals(2, count($tokens));
        $this->assertEquals(2, $tokens[0]->id);
        $this->assertEquals(4, $tokens[1]->id);

        //Token with ID 3 matches with the user with ID
        $token = $this->Tokens->find()->matching('Users', function (Query $q) {
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
        $token = $this->Tokens->save(new Token([
            'token' => 'test1',
            'expiry' => '+1 day',
        ]));
        $this->assertNotEmpty($token);
        $this->assertTrue($token->expiry->isTomorrow());
        $this->assertInstanceOf('Cake\I18n\Time', $token->expiry);

        $token = $this->Tokens->save(new Token([
            'token' => 'test2',
            'extra' => 'testExtra',
        ]));
        $this->assertNotEmpty($token);
        $this->assertEquals('s:9:"testExtra";', $token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'test3',
            'extra' => ['first', 'second'],
        ]));
        $this->assertNotEmpty($token);
        $this->assertEquals('a:2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'test4',
            'extra' => (object)['first', 'second'],
        ]));
        $this->assertNotEmpty($token);
        $this->assertEquals((object)['first', 'second'], unserialize($token->extra));
    }

    /**
     * Test for `deleteExpired()` method
     * @test
     */
    public function testDeleteExpired()
    {
        //Token with ID 2 does not exist anymore
        $count = $this->Tokens->deleteExpired();
        $this->assertEquals(1, $count);
        $this->assertEmpty($this->Tokens->findById(2)->first());

        $this->loadFixtures('Tokens');

        //Same as tokens with ID 2 and 4
        $token = new Token(['user_id' => 2]);

        //Tokens with ID 2 and 4 do not exist anymore
        $count = $this->Tokens->deleteExpired($token);
        $this->assertEquals(2, $count);
        $this->assertEmpty($this->Tokens->find()->where(['OR' => [['id' => 2], ['id' => 4]]])->all());

        $this->loadFixtures('Tokens');

        //Same as token with ID 3
        $token = new Token(['token' => 'token3']);

        //Tokens with ID 2 and 3 do not exist anymore
        $count = $this->Tokens->deleteExpired($token);
        $this->assertEquals(2, $count);
        $this->assertEmpty($this->Tokens->find()->where(['OR' => [['id' => 2], ['id' => 3]]])->all());
    }

    /**
     * Test for `find()` method. It tests that `extra` is formatted
     * @test
     */
    public function testFindFormatsExtraFields()
    {
        $query = $this->Tokens->find();
        $this->assertInstanceOf('Cake\ORM\Query', $query);

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
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM tokens Tokens WHERE expiry >= :c0', $query->sql());
        $this->assertInstanceOf('Cake\I18n\Time', $query->getValueBinder()->bindings()[':c0']['value']);

        //Results are tokens with ID 1, 3 and 4
        $this->assertEquals([1, 3, 4], $query->extract('id')->toArray());
    }

    /**
     * Test for `expired` `find()` method
     * @test
     */
    public function testFindExpired()
    {
        $query = $this->Tokens->find('expired');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM tokens Tokens WHERE expiry < :c0', $query->sql());
        $this->assertInstanceOf('Cake\I18n\Time', $query->getValueBinder()->bindings()[':c0']['value']);

        //Results is token with ID 2
        $this->assertEquals([2], $query->extract('id')->toArray());
    }

    /**
     * Test initialize method
     * @test
     */
    public function testInitialize()
    {
        $this->assertInstanceOf('Tokens\Model\Table\TokensTable', $this->Tokens);
        $this->assertEquals('tokens', $this->Tokens->getTable());
        $this->assertEquals('token', $this->Tokens->getDisplayField());
        $this->assertEquals('id', $this->Tokens->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Tokens->Users);
        $this->assertEquals('user_id', $this->Tokens->Users->getForeignKey());
        $this->assertEquals('Users', $this->Tokens->Users->className());
    }

    /**
     * Test for a custum `Users` table
     * @test
     */
    public function testForCustomUsersTable()
    {
        Configure::write('Tokens.usersClassOptions.className', 'TestApp.Users');

        TableRegistry::clear();
        $this->Tokens = $this->getTable();

        $this->assertEquals('TestApp.Users', $this->Tokens->Users->className());
        $this->assertEquals('This is a test method', $this->Tokens->Users->test());

        $token = $this->Tokens->findById(2)->contain('Users')->first();
        $this->assertInstanceOf('TestApp\Model\Entity\User', $token->user);
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
        $this->getTable()->Users;
    }

    /**
     * Test build rules for `user_id` property
     * @test
     */
    public function testRulesForUserId()
    {
        //Valid `user_id` value
        $token = $this->Tokens->newEntity([
            'user_id' => '2',
            'token' => 'firstToken',
        ]);
        $this->assertNotEmpty($this->Tokens->save($token));
        $this->assertEmpty($token->getErrors());

        //Invalid `user_id` value (the user does not exist)
        $token = $this->Tokens->newEntity([
            'user_id' => '999',
            'token' => 'secondToken',
        ]);
        $this->assertFalse($this->Tokens->save($token));
        $this->assertEquals(['user_id' => ['_existsIn' => 'This value does not exist']], $token->getErrors());
    }

    /**
     * Test for `save()` method
     * @test
     */
    public function testSave()
    {
        $token = $this->Tokens->save(new Token(['token' => 'test1']));
        $this->assertNotEmpty($token);
        $this->assertInstanceOf('Tokens\Model\Entity\Token', $token);
        $this->assertEquals(null, $token->user_id);
        $this->assertRegExp('/^[a-z0-9]{25}$/', $token->token);
        $this->assertEmpty($token->type);
        $this->assertInstanceOf('Cake\I18n\Time', $token->expiry);
        $this->assertEmpty($token->extra);
    }

    /**
     * Test validation for `expiry` property
     * @test
     */
    public function testValidationForExpiry()
    {
        //Valid `expiry` values
        foreach (['Date', 'FrozenDate', 'FrozenTime', 'Time'] as $class) {
            $class = '\Cake\I18n\\' . $class;
            $token = $this->Tokens->newEntity([
                'token' => 'test',
                'expiry' => new $class,
            ]);
            $this->assertEmpty($token->getErrors());
        }

        //Invalid `expiry` value
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'expiry' => 'thisIsAString',
        ]);
        $this->assertEquals(['expiry' => ['dateTime' => 'The provided value is invalid']], $token->getErrors());
    }

    /**
     * Test validation for `token` property
     * @test
     */
    public function testValidationForToken()
    {
        $token = $this->Tokens->newEntity(['token' => 'test']);
        $this->assertEmpty($token->getErrors());

        $token = $this->Tokens->newEntity([]);
        $this->assertEquals(['token' => ['_required' => 'This field is required']], $token->getErrors());
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
        $this->assertEmpty($token->getErrors());

        //Valid `type` value
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => str_repeat('a', 255),
        ]);
        $this->assertEmpty($token->getErrors());

        //Invalid `type` value (it is too short)
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => '12',
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->getErrors());

        //Invalid `type` value (it is too long)
        $token = $this->Tokens->newEntity([
            'token' => 'test',
            'type' => str_repeat('a', 256),
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->getErrors());
    }
}
