<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{
    protected $_virtual = ['test'];

    protected function _getTest(): string
    {
        return 'This is a test property';
    }
}
