<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Doctrine\Repository;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Doctrine\Repository\ChannelRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ChannelRepositoryTest extends TestCase
{
    private ChannelRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelRepository();
    }

}
