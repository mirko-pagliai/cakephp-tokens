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
namespace Tokens\Test\TestCase\Utility;

use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use LogicException;
use MeTools\TestSuite\MockTrait;
use MeTools\TestSuite\TestCase;
use TestApp\TokenTraitClass;

/**
 * TokenTraitTest Test Case
 */
class TokenTraitTest extends TestCase
{
    use MockTrait;

    /**
     * A class that uses the trait
     * @var \TestApp\TokenTraitClass
     */
    public $TokenTrait;

    /**
     * @var \Tokens\Model\Table\TokensTable
     */
    public $Tokens;

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

        $this->Tokens = $this->getTable('Tokens.Tokens');
        $this->TokenTrait = new TokenTraitClass();
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
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $this->assertInstanceOf(Query::class, $this->TokenTrait->find());
    }

    /**
     * Test for `check()` method
     * @test
     */
    public function testCheck()
    {
        //This token does not exist
        $this->assertFalse($this->TokenTrait->check('tokenNotExists'));

        //This token exists, but it has expired
        $this->assertFalse($this->TokenTrait->check('036b303f058a35ed48220ee5h'));

        $value = '553790c2c20b2ec1d2a406b44';

        $this->assertTrue($this->TokenTrait->check($value, ['user_id' => 1, 'type' => 'registration']));

        //Missing `user_id` and `ŧype`
        $this->assertFalse($this->TokenTrait->check($value, []));

        //Missing `ŧype`
        $this->assertFalse($this->TokenTrait->check($value, ['user_id' => 1]));

        //Missing `user_id`
        $this->assertFalse($this->TokenTrait->check($value, ['type' => 'registration']));

        //Wrong type
        $this->assertFalse($this->TokenTrait->check($value, ['user_id' => 1, 'type' => 'invalid']));

        //Wrong user ID
        $this->assertFalse($this->TokenTrait->check($value, ['user_id' => 2, 'type' => 'registration']));
    }

    /**
     * Test for `create()` method
     * @test
     */
    public function testCreate()
    {
        $token = $this->TokenTrait->create('token_1');
        $this->assertNotEmpty($token);
        $token = $this->Tokens->findByToken($token)->contain('Users')->first();
        $this->assertNotEmpty($token);

        $token = $this->TokenTrait->create('token_2', [
            'user_id' => 2,
            'type' => 'testType',
            'extra' => ['extra1', 'extra2'],
            'expiry' => '+1 days',
        ]);
        $this->assertNotEmpty($token);
        $token = $this->Tokens->findByToken($token)->contain('Users')->first();
        $this->assertEquals(2, $token->user->id);
        $this->assertEquals('testType', $token->type);
        $this->assertEquals(['extra1', 'extra2'], $token->extra);
        $this->assertInstanceOf(FrozenTime::class, $token->expiry);
        $this->assertTrue($token->expiry->isTomorrow());

        //With error
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error for `type` field: the provided value is invalid');
        $this->TokenTrait->create('token_3', ['type' => 'aa']);
    }

    /**
     * Test for `delete()` method
     * @test
     */
    public function testDelete()
    {
        //This token does not exist
        $this->assertFalse($this->TokenTrait->delete('tokenNotExists'));

        $this->assertNotEmpty($this->Tokens->findById(3)->first());
        $this->assertTrue($this->TokenTrait->delete('553790c2c20b2ec1d2a406b44'));
        $this->assertEmpty($this->Tokens->findById(3)->first());
    }
}
