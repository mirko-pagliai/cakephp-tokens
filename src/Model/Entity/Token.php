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
namespace Tokens\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\Utility\Security;

/**
 * Token Entity
 * @property int $id
 * @property string $type
 * @property string $token
 * @property string $extra
 * @property \Cake\I18n\Time $expiry
 */
class Token extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * `set` mutators for `expiry` property
     * @param string|object $expiry `expiry` value. Can be a string or a time
     *  instance. If the value is a string, it gets the class to use from the
     *  configuration
     * @return string
     * @see http://book.cakephp.org/3.0/en/core-libraries/time.html#creating-time-instances
     * @see http://book.cakephp.org/3.0/en/core-libraries/time.html#immutable-dates-and-times
     */
    protected function _setExpiry($expiry)
    {
        if (is_object($expiry) && in_array(get_class($expiry), [
            'Cake\I18n\Date',
            'Cake\I18n\Time',
            'Cake\I18n\FrozenDate',
            'Cake\I18n\FrozenTime',
        ])) {
            return $expiry;
        }

        //If the value is a string, it gets the class to use from the
        //  configuration
        $class = Configure::read('Tokens.expiryDefaultClass');

        return new $class($expiry);
    }

    /**
     * `set` mutators for `token` property
     * @param string $token `token` value
     * @return string
     */
    protected function _setToken($token)
    {
        if (!is_string($token)) {
            $token = serialize($token);
        }

        return substr(Security::hash($token, 'sha1', Configure::read('Tokens.tokenSalt')), 0, 25);
    }
}
