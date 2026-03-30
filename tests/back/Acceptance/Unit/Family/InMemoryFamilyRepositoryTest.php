<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\Family;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryFamilyRepositoryTest extends TestCase
{
    private InMemoryFamilyRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryFamilyRepository();
    }

}
