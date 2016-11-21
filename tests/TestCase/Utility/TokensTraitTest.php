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

    public function getFind($token = null, $user = null, $type = null)
    {
        return $this->_find($token, $user, $type);
    }

    public function getTable()
    {
        return $this->_getTable();
    }
}

class TokenTraitTest extends TestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = ['plugin.tokens.tokens'];

    /**
     * @var \Tokens\Model\Table\TokensTable
     */
    public $table;

    /**
     * Instance of the trait
     * @var \TokenTrait
     */
    public $trait;

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->table = TableRegistry::get('Tokens', ['className' => 'Tokens\Model\Table\TokensTable']);
        $this->trait = new TokenTrait;
    }

    /**
     * tearDown method
     * @return void
     */
    public function tearDown()
    {
        unset($this->table, $this->trait);

        parent::tearDown();
    }

    /**
     * Test for `_find()` method
     * @test
     */
    public function testFind()
    {
        $this->assertEquals('Cake\ORM\Query', get_class($this->trait->getFind()));
    }

    /**
     * Test for `_getTable()` method
     * @test
     */
    public function testGetTable()
    {
        $this->assertEquals('Tokens\Model\Table\TokensTable', get_class($this->trait->getTable()));
    }

    /**
     * Test for `check()` method
     * @test
     */
    public function testCheck()
    {
        //This token does not exist
        $this->assertFalse($this->trait->check('tokenNotExists'));

        //This token exists, but it has expired
        $this->assertFalse($this->trait->check('036b303f058a35ed48220ee5h'));

        $this->assertTrue($this->trait->check('c658ffdd8d26875d2539cf78c'));
        $this->assertTrue($this->trait->check('c658ffdd8d26875d2539cf78c', 1));
        $this->assertTrue($this->trait->check('c658ffdd8d26875d2539cf78c', null, 'registration'));
        $this->assertTrue($this->trait->check('c658ffdd8d26875d2539cf78c', 1, 'registration'));

        //Wrong user ID
        $this->assertFalse($this->trait->check('c658ffdd8d26875d2539cf78c', 2));
        //Wrong type
        $this->assertFalse($this->trait->check('c658ffdd8d26875d2539cf78c', 1, 'Invalid'));
        $this->assertFalse($this->trait->check('c658ffdd8d26875d2539cf78c', null, 'Invalid'));
    }

    /**
     * Test for `delete()` method
     * @test
     */
    public function testDelete()
    {
        //This token does not exist
        $this->assertFalse($this->trait->delete('tokenNotExists'));

        $this->assertNotEmpty($this->table->findById(3)->first());
        $this->assertTrue($this->trait->delete('c658ffdd8d26875d2539cf78c'));
        $this->assertEmpty($this->table->findById(3)->first());
    }
}
