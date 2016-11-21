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
     * Test for `_getTable()` method
     * @test
     */
    public function testGetTable()
    {
        $this->assertEquals('Tokens\Model\Table\TokensTable', get_class($this->trait->getTable()));
    }

    /**
     * Test for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->assertFalse($this->trait->delete('tokenNotExists'));

        $this->assertTrue($this->trait->delete('036b303f058a35ed48220ee5f'));
    }
}
