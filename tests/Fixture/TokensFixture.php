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
namespace Tokens\Test\Fixture;

use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * TokensFixture
 */
class TokensFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'user_id' => ['type' => 'integer', 'length' => 11, 'null' => true, 'default' => null, 'autoIncrement' => null],
        'token' => ['type' => 'string', 'length' => 25, 'null' => false, 'default' => null],
        'type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null],
        'extra' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null],
        'expiry' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        '_indexes' => [
            'user_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
            'type' => ['type' => 'index', 'columns' => ['type'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ];

    /**
     * Init. Adds some records
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => null,
                'token' => '036b303f058a35ed48220ee5f',
                'type' => null,
                'extra' => null,
                'expiry' => new FrozenTime('+1 day'),
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'token' => '553790c2c20b2ec1d2a406b44',
                'type' => 'registration',
                'extra' => 'a:2:{i:0;s:5:"first";i:1;s:6:"second";}',
                'expiry' => new FrozenTime('+2 day'),
            ],
            [
                'id' => 4,
                'user_id' => 2,
                'token' => '036b303f058a35ed48220ee5i',
                'type' => null,
                'extra' => 'O:8:"stdClass":2:{i:0;s:5:"first";i:1;s:6:"second";}',
                'expiry' => new FrozenTime('+1 month'),
            ],
            //Expired token. It is added last, with ID 2, so that it is not deleted
            [
                'id' => 2,
                'user_id' => 2,
                'token' => '036b303f058a35ed48220ee5h',
                'type' => null,
                'extra' => 's:9:"testExtra";',
                'expiry' => new FrozenTime('-1 day'),
            ],
        ];

        parent::init();
    }
}
