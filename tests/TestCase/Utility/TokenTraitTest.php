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
namespace Tokens\Test\TestCase\Utility;

use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Reflection\ReflectionTrait;
use Tokens\Controller\Component\TokenComponent;

/**
 * TokenTraitTest Test Case
 */
class TokenTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * A class that uses the trait
     * @var \TokenTrait
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
        'core.users',
        'plugin.tokens.tokens',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tokens = TableRegistry::get('Tokens.Tokens');
        $this->TokenTrait = new TokenComponent(new ComponentRegistry);
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
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $this->assertInstanceOf('Cake\ORM\Query', $this->invokeMethod($this->TokenTrait, 'find'));
    }

    /**
     * Test for `getTable()` method
     * @test
     */
    public function testGetTable()
    {
        $this->assertInstanceOf('Tokens\Model\Table\TokensTable', $this->invokeMethod($this->TokenTrait, 'getTable'));
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

        $value = 'c658ffdd8d26875d2539cf78c';

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
        $this->assertInstanceOf('Cake\I18n\Time', $token->expiry);
        $this->assertTrue($token->expiry->isTomorrow());
    }

    /**
     * Test for `create()` method, with error
     * @expectedException LogicException
     * @expectedExceptionMessage Error for `type` field: the provided value is invalid
     * @test
     */
    public function testCreateWithError()
    {
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
        $this->assertTrue($this->TokenTrait->delete('c658ffdd8d26875d2539cf78c'));
        $this->assertEmpty($this->Tokens->findById(3)->first());
    }
}
