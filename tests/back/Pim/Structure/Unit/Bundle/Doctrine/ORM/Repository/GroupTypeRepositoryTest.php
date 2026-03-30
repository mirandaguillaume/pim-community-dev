<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\GroupTypeRepository;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class GroupTypeRepositoryTest extends TestCase
{
    private GroupTypeRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupTypeRepository();
    }

}
