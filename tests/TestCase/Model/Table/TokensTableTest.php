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

        $this->Tokens = $this->Tokens ?: $this->getTable('Tokens.Tokens');
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
    public function testAssociationWithUsers(): void
    {
        //Token with ID 1 has no user
        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->findById(1)->contain('Users')->first();
        $this->assertEmpty($token->get('user'));

        //Token with ID 3 has user with ID 2
        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->findById(3)->contain('Users')->first();
        $this->assertInstanceOf(Entity::class, $token->get('user'));
        $this->assertEquals(1, $token->get('user')->get('id'));

        //User with ID 2 has tokens with ID 3 and 4
        /** @var \TestApp\Model\Table\UsersTable $Users */
        $Users = $this->Tokens->Users;
        $tokens = $Users->findById(2)->contain('Tokens')->extract('tokens')->first();
        $this->assertEquals(2, count($tokens));
        $this->assertEquals(2, $tokens[0]->get('id'));
        $this->assertEquals(4, $tokens[1]->get('id'));

        //Token with ID 3 matches with the user with ID
        $token = $this->Tokens->find()->matching('Users', function (Query $query) {
            return $query->where(['Users.id' => 1]);
        })->extract('id')->toArray();
        $this->assertEquals([3], $token);
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave(): void
    {
        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->save(new Token(['token' => 'test1', 'expiry' => '+1 day']));
        $this->assertNotEmpty($token);
        $this->assertTrue($token->get('expiry')->isTomorrow());
        $this->assertInstanceOf(Time::class, $token->get('expiry'));

        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->save(new Token(['token' => 'test2', 'extra' => 'testExtra']));
        $this->assertNotEmpty($token);
        $this->assertEquals('s:9:"testExtra";', $token->get('extra'));

        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->save(new Token([
            'token' => 'test3',
            'extra' => ['first', 'second'],
        ]));
        $this->assertNotEmpty($token);
        $this->assertEquals('a:2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->get('extra'));

        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->save(new Token([
            'token' => 'test4',
            'extra' => (object)['first', 'second'],
        ]));
        $this->assertNotEmpty($token);
        $this->assertEquals((object)['first', 'second'], unserialize($token->get('extra')));
    }

    /**
     * Test for `deleteExpired()` method
     * @test
     */
    public function testDeleteExpired(): void
    {
        //Token with ID 2 does not exist anymore
        $this->assertEquals(1, $this->Tokens->deleteExpired());
        $this->assertEmpty($this->Tokens->findById(2)->first());
    }

    /**
     * Test for `deleteExpired()` method, with an user id
     * @test
     */
    public function testDeleteExpiredWithUserId(): void
    {
        //`user_id` equal to the tokens with ID 2 and 4
        //Tokens with ID 2 and 4 do not exist anymore
        $this->assertEquals(2, $this->Tokens->deleteExpired(new Token(['user_id' => 2])));
        $this->assertEmpty($this->Tokens->find()->where(['OR' => [['id' => 2], ['id' => 4]]])->count());
    }

    /**
     * Test for `deleteExpired()` method, with a token value
     * @test
     */
    public function testDeleteExpiredWithTokenValue(): void
    {
        //`token` equal to the token with ID 3
        //Tokens with ID 2 and 3 do not exist anymore
        $this->assertEquals(2, $this->Tokens->deleteExpired(new Token(['token' => 'token3'])));
        $this->assertEmpty($this->Tokens->find()->where(['OR' => [['id' => 2], ['id' => 3]]])->count());
    }

    /**
     * Test for `find()` method. It tests that `extra` is formatted
     * @test
     */
    public function testFindFormatsExtraFields(): void
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
    public function testFindActive(): void
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
    public function testFindExpired(): void
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
    public function testInitialize(): void
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
        $Tokens = $this->getTable('Tokens.Tokens', compact('usersClassOptions'));
        /** @var \Tokens\Model\Table\TokensTable $Tokens */
        $this->assertInstanceOf(BelongsTo::class, $Tokens->Users);
        $this->assertEquals('user_id', $Tokens->Users->getForeignKey());
        $this->assertEquals('AnotherUserTable', $Tokens->Users->getClassName());
    }

    /**
     * Test for a custum `Users` table
     * @test
     */
    public function testForCustomUsersTable(): void
    {
        /** @var \Tokens\Model\Table\TokensTable $Tokens */
        $Tokens = $this->getTable('Tokens.Tokens', ['usersClassOptions' => ['className' => 'TestApp.Users']]);
        $this->assertEquals('TestApp.Users', $Tokens->Users->getClassName());

        /** @var \Tokens\Model\Entity\Token $token */
        $token = $Tokens->findById(2)->contain('Users')->first();
        $this->assertInstanceOf(User::class, $token->get('user'));
        $this->assertEquals('This is a test property', $token->get('user')->get('test'));
    }

    /**
     * Test for a no `Users` table
     * @test
     */
    public function testForNoUsersTable(): void
    {
        $expected = version_compare(Configure::version(), '4.1', '>=') ? 'The `Users` association is not defined on `Tokens`.' : 'The Users association is not defined on Tokens.';
        $this->expectExceptionMessage($expected);
        Configure::write('Tokens.usersClassOptions', false);
        /** @var \Tokens\Model\Table\TokensTable $Tokens */
        $Tokens = $this->getTable('Tokens.Tokens');
        $Tokens->getAssociation('Users');
    }

    /**
     * Test build rules for `user_id` property
     * @test
     */
    public function testRulesForUserId(): void
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
    public function testSave(): void
    {
        /** @var \Tokens\Model\Entity\Token $token */
        $token = $this->Tokens->save(new Token(['token' => 'test1']));
        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals(null, $token->get('user_id'));
        $this->assertMatchesRegularExpression('/^[\w\d]{25}$/', $token->get('token'));
        $this->assertEmpty($token->get('type'));
        $this->assertInstanceOf(Time::class, $token->get('expiry'));
        $this->assertEmpty($token->get('extra'));
    }

    /**
     * Test validation for `expiry` property
     * @test
     */
    public function testValidationForExpiry(): void
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
    public function testValidationForToken(): void
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
    public function testValidationForType(): void
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
