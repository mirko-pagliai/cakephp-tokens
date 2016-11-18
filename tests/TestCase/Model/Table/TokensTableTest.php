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
use Tokens\Model\Entity\Token;

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

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $token = $this->Tokens->save(new Token(['token' => 'token1']));

        $this->assertNotEmpty($token);
        $this->assertEquals('Tokens\Model\Entity\Token', get_class($token));
        $this->assertEmpty($token->type);
        $this->assertRegExp('/^[a-z0-9]{25}$/', $token->token);
        $this->assertEquals('Cake\I18n\Time', get_class($token->expiry));
        $this->assertEmpty($token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'token2',
            'expiry' => '+1 day',
        ]));

        $this->assertNotEmpty($token);
        $this->assertTrue($token->expiry->isTomorrow());
        $this->assertEquals('Cake\I18n\Time', get_class($token->expiry));

        $token = $this->Tokens->save(new Token([
            'type' => 'testType',
            'token' => 'token3',
            'extra' => 'testExtra',
        ]));

        $this->assertNotEmpty($token);
        $this->assertEquals('testType', $token->type);
        $this->assertEquals('s:9:"testExtra";', $token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'token4',
            'extra' => ['first', 'second'],
        ]));

        $this->assertNotEmpty($token);
        $this->assertEquals('a:2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);

        $token = $this->Tokens->save(new Token([
            'token' => 'token5',
            'extra' => (object)['first', 'second'],
        ]));

        $this->assertNotEmpty($token);
        $this->assertEquals('O:8:"stdClass":2:{i:0;s:5:"first";i:1;s:6:"second";}', $token->extra);
    }

    /**
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $token = $this->Tokens->get(1);

        $this->assertNotEmpty($token);
        $this->assertNull($token->extra);

        $token = $this->Tokens->get(2);

        $this->assertNotEmpty($token);
        $this->assertEquals('testExtra', $token->extra);

        $token = $this->Tokens->get(3);

        $this->assertNotEmpty($token);
        $this->assertEquals(['first', 'second'], $token->extra);

        $token = $this->Tokens->get(4);

        $this->assertNotEmpty($token);
        $this->assertEquals((object)['first', 'second'], $token->extra);
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
     * Test validation. Generic method
     * @test
     */
    public function testValidation()
    {
        $token = $this->Tokens->newEntity(['token' => 'test']);
        $this->assertEmpty($token->errors());
    }

    /**
     * Test validation for `type` property
     * @test
     */
    public function testValidationType()
    {
        $token = $this->Tokens->newEntity([
            'type' => '12',
            'token' => 'test',
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->errors());

        $token = $this->Tokens->newEntity([
            'type' => '123',
            'token' => 'test',
        ]);
        $this->assertEmpty($token->errors());

        $token = $this->Tokens->newEntity([
            'type' => str_repeat('a', 256),
            'token' => 'test',
        ]);
        $this->assertEquals(['type' => ['lengthBetween' => 'The provided value is invalid']], $token->errors());

        $token = $this->Tokens->newEntity([
            'type' => str_repeat('a', 255),
            'token' => 'test',
        ]);
        $this->assertEmpty($token->errors());
    }

    /**
     * Test validation for `token` property
     * @test
     */
    public function testValidationToken()
    {
        $token = $this->Tokens->newEntity([]);
        $this->assertEquals(['token' => ['_required' => 'This field is required']], $token->errors());

        $this->assertNotEmpty($this->Tokens->save(new Token(['token' => 'uniqueValue'])));

        $token = new Token(['token' => 'uniqueValue']);
        $this->assertFalse($this->Tokens->save($token));
        $this->assertEquals(['token' => ['_isUnique' => 'This value is already in use']], $token->errors());
    }
}
