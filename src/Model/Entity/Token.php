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
namespace Tokens\Model\Entity;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\Utility\Security;
use DateTimeInterface;

/**
 * Token Entity
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $type
 * @property string $extra
 * @property \Cake\I18n\Time $expiry
 * @property \Tokens\Model\Entity\User $user
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
     * @param \DateTimeInterface|string $expiry `expiry` value. Can be a string or a time
     *  instance. If the value is a string, an instance of `Cake\I18n\Time` will
     *  be created
     * @return \DateTimeInterface
     */
    protected function _setExpiry($expiry): DateTimeInterface
    {
        return is_object($expiry) && method_exists($expiry, 'i18nFormat') ? $expiry : new Time($expiry);
    }

    /**
     * `set` mutators for `token` property
     * @param mixed $token Value. If it's not a string, it will be serialized
     * @return mixed
     */
    protected function _setToken($token)
    {
        //Prevents an empty value is serialized
        if (!$token) {
            return $token;
        }
        $token = is_string($token) ? $token : serialize($token);

        return substr(Security::hash($token, 'sha1', Configure::read('Tokens.tokenSalt')), 0, 25);
    }
}
