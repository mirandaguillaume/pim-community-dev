<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Doctrine\ORM\Repository;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRepositoryTest extends TestCase
{
    private UserRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new UserRepository();
    }

}
