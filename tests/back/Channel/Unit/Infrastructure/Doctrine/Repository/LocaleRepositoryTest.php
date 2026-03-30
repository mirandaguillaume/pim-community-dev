<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Doctrine\Repository;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Channel\Infrastructure\Doctrine\Repository\LocaleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;

class LocaleRepositoryTest extends TestCase
{
    private LocaleRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleRepository();
    }

}
