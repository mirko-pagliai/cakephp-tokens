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
use Cake\I18n\FrozenTime;
use Cake\I18n\I18nDateTimeInterface;
use Cake\ORM\Entity;
use Cake\Utility\Security;

/**
 * Token Entity
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $type
 * @property string $extra
 * @property \Cake\I18n\FrozenTime $expiry
 * @property \Tokens\Model\Entity\User $user
 */
class Token extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array<string, bool>
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * `set` mutators for `expiry` property
     * @param \Cake\I18n\I18nDateTimeInterface|string $expiry `expiry` value. Can be a string or a time instance. If the
     *  value is a string, an instance of `FrozenTime` will be created
     * @return \Cake\I18n\I18nDateTimeInterface
     */
    protected function _setExpiry($expiry): I18nDateTimeInterface
    {
        return is_object($expiry) && method_exists($expiry, 'i18nFormat') ? $expiry : new FrozenTime($expiry);
    }

    /**
     * `set` mutators for `token` property
     * @param mixed $token Value. If it's not a string, it will be serialized
     * @return string
     */
    protected function _setToken($token): string
    {
        //Prevents an empty value is serialized
        if (!$token) {
            return '';
        }
        $token = is_string($token) ? $token : serialize($token);

        return substr(Security::hash($token, 'sha1', Configure::read('Tokens.tokenSalt')), 0, 25);
    }
}
