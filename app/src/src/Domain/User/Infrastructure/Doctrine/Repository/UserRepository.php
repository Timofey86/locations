<?php

declare(strict_types=1);

namespace App\Domain\User\Infrastructure\Doctrine\Repository;

use App\Domain\User\Entity\User;
use App\Infrastructure\Doctrine\Repository\Repository;

class UserRepository extends Repository
{
    protected string $entityClass = User::class;
    protected string $alias = 'users';
}
