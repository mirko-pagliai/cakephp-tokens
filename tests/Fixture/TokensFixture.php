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
namespace Tokens\Test\Fixture;

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
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'token' => ['type' => 'string', 'length' => 25, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'extra' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'expiry' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'user_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
            'type' => ['type' => 'index', 'columns' => ['type'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'token' => ['type' => 'unique', 'columns' => ['token'], 'length' => []],
        ],
    ];

    /**
     * Records
     * @var array
     */
    public $records = [
        ['user_id' => null, 'token' => '036b303f058a35ed48220ee5f', 'type' => null, 'extra' => null, 'expiry' => '11/18/16, 10:30 AM'],
        ['user_id' => 1, 'token' => '036b303f058a35ed48220ee5g', 'type' => null, 'extra' => 's:9:"testExtra";', 'expiry' => '11/18/16, 10:31 AM'],
        ['user_id' => 2, 'token' => '036b303f058a35ed48220ee5h', 'type' => null, 'extra' => 'a:2:{i:0;s:5:"first";i:1;s:6:"second";}', 'expiry' => '11/18/16, 10:32 AM'],
        ['user_id' => 2, 'token' => '036b303f058a35ed48220ee5i', 'type' => null, 'extra' => 'O:8:"stdClass":2:{i:0;s:5:"first";i:1;s:6:"second";}', 'expiry' => '11/18/16, 10:33 AM'],
    ];
}
