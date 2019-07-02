<?php
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

use Cake\Core\Configure;

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

//`Users` class options
if (!Configure::check('Tokens.usersClassOptions')) {
    Configure::write('Tokens.usersClassOptions', [
        'foreignKey' => 'user_id',
        'className' => 'Users',
    ]);
}
