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
namespace Tokens\Test\TestCase\Utility;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Tokens\Utility\TokenTrait as BaseTokenTrait;

/**
 * Makes public some protected methods/properties from `TokenTrait`
 */
class TokenTrait
{
    use BaseTokenTrait;

    public function getFind(array $conditions = [])
    {
        return $this->_find($conditions);
    }

    public function getTable()
    {
        return $this->_getTable();
    }
}

/**
 * Tokens\Utility\TokenTrait Test Case
 */
class TokenTraitTest extends TestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'core.users',
        'plugin.tokens.tokens',
    ];

    /**
     * Instance of the trait
     * @var \TokenTrait
     */
    public $TokenTrait;

    /**
     * @var \Tokens\Model\Table\TokensTable
     */
    public $Tokens;

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tokens = TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);
        $this->TokenTrait = new TokenTrait;
    }

    /**
     * tearDown method
     * @return void
     */
    public function tearDown()
    {
        $this->Tokens->deleteAll(['id >=' => 1]);

        unset($this->Tokens, $this->TokenTrait);

        parent::tearDown();
    }

    /**
     * Test for `_find()` method
     * @test
     */
    public function testFind()
    {
        $this->assertInstanceOf('Cake\ORM\Query', $this->TokenTrait->getFind());
    }

    /**
     * Test for `_getTable()` method
     * @test
     */
    public function testGetTable()
    {
        $this->assertInstanceOf('Tokens\Model\Table\TokensTable', $this->TokenTrait->getTable());
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
     * @expectedException Cake\Network\Exception\InternalErrorException
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
