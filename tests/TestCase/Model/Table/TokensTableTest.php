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

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Tokens\Model\Table\TokensTable;

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
    public $fixtures = ['plugin.tokens.tokens'];

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tokens = TableRegistry::get('Tokens.Tokens');
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
    
    public function testSave()
    {
        //Saves a new entity
        $newEntity = $this->Tokens->newEntity();
        $newEntity->token = 'test';
        $savedEntity = $this->Tokens->save($newEntity);

        $this->assertNotEmpty($savedEntity);
        $this->assertEquals('Tokens\Model\Entity\Token', get_class($savedEntity));
        $this->assertEquals('Cake\I18n\Time', get_class($savedEntity->expiry));
    }

    /**
     * Test initialize method
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
