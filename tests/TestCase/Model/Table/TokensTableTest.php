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
namespace Tokens\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use MeTools\TestSuite\TestCase;
use TestApp\Model\Entity\User;
use Tokens\Model\Entity\Token;
use Tokens\Model\Table\TokensTable;

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
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Tokens = $this->getMockForModel('Tokens.Tokens', null);
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->Tokens->deleteAll(['id >=' => 1]);
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
        $this->assertInstanceOf(Entity::class, $token->user);
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
        $token = $this->Tokens->save(new Token(['token' => 'test1', 'expiry' => '+1 day']));
        $this->assertNotEmpty($token);
        $this->assertTrue($token->expiry->isTomorrow());
        $this->assertInstanceOf(Time::class, $token->expiry);

        $token = $this->Tokens->save(new Token(['token' => 'test2', 'extra' => 'testExtra']));
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
        $this->assertEquals(1, $this->Tokens->deleteExpired());
        $this->assertEmpty($this->Tokens->findById(2)->first());

        //`user_id` equal to the tokens with ID 2 and 4
        //Tokens with ID 2 and 4 do not exist anymore
        $this->loadFixtures('Tokens');
        $this->assertEquals(2, $this->Tokens->deleteExpired(new Token(['user_id' => 2])));
        $this->assertEmpty($this->Tokens->find()->where(['OR' => [['id' => 2], ['id' => 4]]])->all());

        //`user_id` equal to the token with ID 3
        //Tokens with ID 2 and 3 do not exist anymore
        $this->loadFixtures('Tokens');
        $this->assertEquals(2, $this->Tokens->deleteExpired(new Token(['token' => 'token3'])));
        $this->assertEmpty($this->Tokens->find()->where(['OR' => [['id' => 2], ['id' => 3]]])->all());
    }

    /**
     * Test for `find()` method. It tests that `extra` is formatted
     * @test
     */
    public function testFindFormatsExtraFields()
    {
        $query = $this->Tokens->find();
        $this->assertInstanceOf(Query::class, $query);

        $expected = [
            null,
            'testExtra',
            ['first', 'second'],
            (object)['first', 'second'],
        ];
        $this->assertEquals($expected, $query->extract('extra')->toArray());
    }

    /**
     * Test for `active` `find()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Tokens->find('active');
        $this->assertInstanceOf(Query::class, $query);
        $this->assertStringEndsWith('FROM tokens Tokens WHERE expiry >= :c0', $query->sql());
        $this->assertInstanceOf(Time::class, $query->getValueBinder()->bindings()[':c0']['value']);

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
        $this->assertInstanceOf(Query::class, $query);
        $this->assertStringEndsWith('FROM tokens Tokens WHERE expiry < :c0', $query->sql());
        $this->assertInstanceOf(Time::class, $query->getValueBinder()->bindings()[':c0']['value']);

        //Results is token with ID 2
        $this->assertEquals([2], $query->extract('id')->toArray());
    }

    /**
     * Test initialize method
     * @test
     */
    public function testInitialize()
    {
        $this->assertInstanceOf(TokensTable::class, $this->Tokens);
        $this->assertEquals('tokens', $this->Tokens->getTable());
        $this->assertEquals('token', $this->Tokens->getDisplayField());
        $this->assertEquals('id', $this->Tokens->getPrimaryKey());

        $this->assertInstanceOf(BelongsTo::class, $this->Tokens->Users);
        $this->assertEquals('user_id', $this->Tokens->Users->getForeignKey());
        $this->assertEquals('Users', $this->Tokens->Users->getClassName());

        //Using another table
        $usersClassOptions = ['className' => 'AnotherUserTable', 'foreignKey' => 'user_id'];
        $Tokens = $this->getMockForModel('Tokens.Tokens', null, compact('usersClassOptions'));
        $this->assertInstanceOf(BelongsTo::class, $Tokens->Users);
        $this->assertEquals('user_id', $Tokens->Users->getForeignKey());
        $this->assertEquals('AnotherUserTable', $Tokens->Users->getClassName());
    }

    /**
     * Test for a custum `Users` table
     * @test
     */
    public function testForCustomUsersTable()
    {
        $Tokens = $this->getMockForModel('Tokens.Tokens', null, ['usersClassOptions' => ['className' => 'TestApp.Users']]);

        $this->assertEquals('TestApp.Users', $Tokens->Users->getClassName());
        $this->assertEquals('This is a test method', $Tokens->Users->test());

        $token = $Tokens->findById(2)->contain('Users')->first();
        $this->assertInstanceOf(User::class, $token->user);
        $this->assertEquals('This is a test property', $token->user->test);
    }

    /**
     * Test for a no `Users` table
     * @test
     */
    public function testForNoUsersTable()
    {
        Configure::write('Tokens.usersClassOptions', false);
        $Tokens = $this->getMockForModel('Tokens.Tokens', null);
        $this->expectExceptionMessage('The Users association is not defined on Tokens.');
        $Tokens->getAssociation('Users');
    }

    /**
     * Test build rules for `user_id` property
     * @test
     */
    public function testRulesForUserId()
    {
        //Valid `user_id` value
        $token = $this->Tokens->newEntity(['user_id' => '2', 'token' => 'firstToken']);
        $this->assertNotEmpty($this->Tokens->save($token));
        $this->assertEmpty($token->getErrors());

        //Invalid `user_id` value (the user does not exist)
        $token = $this->Tokens->newEntity(['user_id' => '999', 'token' => 'secondToken']);
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
        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals(null, $token->user_id);
        $this->assertRegExp('/^[\w\d]{25}$/', $token->token);
        $this->assertEmpty($token->type);
        $this->assertInstanceOf(Time::class, $token->expiry);
        $this->assertEmpty($token->extra);
    }

    /**
     * Test validation for `expiry` property
     * @test
     */
    public function testValidationForExpiry()
    {
        //Valid `expiry` values
        foreach ([Date::class, FrozenDate::class, FrozenTime::class, Time::class] as $class) {
            $token = $this->Tokens->newEntity(['token' => 'test', 'expiry' => new $class()]);
            $this->assertEmpty($token->getErrors());
        }

        //Invalid `expiry` value
        $token = $this->Tokens->newEntity(['token' => 'test', 'expiry' => 'string']);
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
        $token = $this->Tokens->newEntity(['token' => 'test', 'type' => '123']);
        $this->assertEmpty($token->getErrors());

        //Valid `type` value
        $token = $this->Tokens->newEntity(['token' => 'test', 'type' => str_repeat('a', 255)]);
        $this->assertEmpty($token->getErrors());

        //Invalid `type` value (it is too short)
        $token = $this->Tokens->newEntity(['token' => 'test', 'type' => '12']);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->getErrors());

        //Invalid `type` value (it is too long)
        $token = $this->Tokens->newEntity(['token' => 'test', 'type' => str_repeat('a', 256)]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->getErrors());
    }
}
