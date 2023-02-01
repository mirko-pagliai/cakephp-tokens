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
use Tokens\Model\Table\TokensTable;

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
    public TokensTable $Tokens;

    /**
     * Fixtures
     * @var array<string>
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

        if (empty($this->Tokens)) {
            /** @var \Tokens\Model\Table\TokensTable $Tokens */
            $Tokens = $this->getTable('Tokens.Tokens');
            $this->Tokens = $Tokens;
        }
        $this->TokenTrait = $this->TokenTrait ?: new TokenTraitClass();
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
     * @test
     * @uses \Tokens\Utility\TokenTrait::find()
     */
    public function testFind(): void
    {
        $this->assertInstanceOf(Query::class, $this->TokenTrait->find());
    }

    /**
     * @test
     * @uses \Tokens\Utility\TokenTrait::check()
     */
    public function testCheck(): void
    {
        //This token does not exist
        $this->assertFalse($this->TokenTrait->check('tokenNotExists'));

        //This token exists, but it has expired
        $this->assertFalse($this->TokenTrait->check('036b303f058a35ed48220ee5h'));

        $value = '553790c2c20b2ec1d2a406b44';

        $this->assertTrue($this->TokenTrait->check($value, ['user_id' => 1, 'type' => 'registration']));

        //Missing `user_id` and `type`
        $this->assertFalse($this->TokenTrait->check($value, []));

        //Missing `type`
        $this->assertFalse($this->TokenTrait->check($value, ['user_id' => 1]));

        //Missing `user_id`
        $this->assertFalse($this->TokenTrait->check($value, ['type' => 'registration']));

        //Wrong type
        $this->assertFalse($this->TokenTrait->check($value, ['user_id' => 1, 'type' => 'invalid']));

        //Wrong user ID
        $this->assertFalse($this->TokenTrait->check($value, ['user_id' => 2, 'type' => 'registration']));
    }

    /**
     * @test
     * @uses \Tokens\Utility\TokenTrait::create()
     */
    public function testCreate(): void
    {
        $Token = $this->TokenTrait->create('token_1');
        $this->assertNotEmpty($Token);
        $Token = $this->Tokens->findByToken($Token)->contain('Users')->first();
        $this->assertNotEmpty($Token);

        $Token = $this->TokenTrait->create('token_2', [
            'user_id' => 2,
            'type' => 'testType',
            'extra' => ['extra1', 'extra2'],
            'expiry' => '+1 days',
        ]);
        $this->assertNotEmpty($Token);
        $Query = $this->Tokens->findByToken($Token);
        /** @var \Tokens\Model\Entity\Token $Token */
        $Token = $Query->contain('Users')->first();
        $this->assertEquals(2, $Token->get('user')->get('id'));
        $this->assertEquals('testType', $Token->get('type'));
        $this->assertEquals(['extra1', 'extra2'], $Token->get('extra'));
        $this->assertInstanceOf(FrozenTime::class, $Token->get('expiry'));
        $this->assertTrue($Token->get('expiry')->isTomorrow());

        //With error
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error for `type` field: the provided value is invalid');
        $this->TokenTrait->create('token_3', ['type' => 'aa']);
    }

    /**
     * @test
     * @uses \Tokens\Utility\TokenTrait::delete()
     */
    public function testDelete(): void
    {
        //This token does not exist
        $this->assertFalse($this->TokenTrait->delete('tokenNotExists'));

        $this->assertNotEmpty($this->Tokens->findById(3)->first());
        $this->assertTrue($this->TokenTrait->delete('553790c2c20b2ec1d2a406b44'));
        $this->assertEmpty($this->Tokens->findById(3)->first());
    }
}
