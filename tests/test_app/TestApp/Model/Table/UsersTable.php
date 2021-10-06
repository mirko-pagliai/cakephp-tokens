<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

/**
 * UsersTable
 * @method \Cake\ORM\Query findById(int $id)
 */
class UsersTable extends Table
{
    public function test(): string
    {
        return 'This is a test method';
    }
}
