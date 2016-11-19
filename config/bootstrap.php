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
use Cake\Core\Configure;

//Default class for `expiry`
if (!Configure::check('Tokens.expiryDefaultClass')) {
    Configure::write('Tokens.expiryDefaultClass', 'Cake\I18n\FrozenTime');
}

//Default value for `expiry`.
//For supported formats, see http://php.net/manual/en/datetime.formats.php.
//In particular, see relative formats http://php.net/manual/en/datetime.formats.relative.php
if (!Configure::check('Tokens.expiryDefaultValue')) {
    Configure::write('Tokens.expiryDefaultValue', '+1 hour');
}

//Salt to use to generate the token. Can be a string or a boolean.
//With `true`, the application’s salt value will be used.
if (!Configure::check('Tokens.tokenSalt')) {
    Configure::write('Tokens.tokenSalt', true);
}

//Checks for default class for `expiry`
if (empty(Configure::read('Tokens.expiryDefaultClass')) || !in_array(Configure::read('Tokens.expiryDefaultClass'), [
    'Cake\I18n\Date',
    'Cake\I18n\Time',
    'Cake\I18n\FrozenDate',
    'Cake\I18n\FrozenTime',
])) {
    trigger_error('Invalid default class for `expiry`', E_USER_ERROR);
}
