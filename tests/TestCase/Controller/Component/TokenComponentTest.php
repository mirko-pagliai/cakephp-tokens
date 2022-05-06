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
namespace Tokens\Test\TestCase\Controller\Component;

use MeTools\TestSuite\TestCase;
use ReflectionClass;
use Tokens\Controller\Component\TokenComponent;

/**
 * TokenComponentTest Test Case
 */
class TokenComponentTest extends TestCase
{
    /**
     * Checks the Component has the trait
     * @return void
     */
    public function testHasTrait(): void
    {
        $reflection = new ReflectionClass(TokenComponent::class);
        $this->assertSame('Tokens\Utility\TokenTrait', array_value_first($reflection->getTraits())->getName());
    }
}
