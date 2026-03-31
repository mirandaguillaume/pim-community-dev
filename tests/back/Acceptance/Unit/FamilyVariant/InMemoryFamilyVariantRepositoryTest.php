<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\FamilyVariant;

use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\FamilyVariant\InMemoryFamilyVariantRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryFamilyVariantRepositoryTest extends TestCase
{
    private InMemoryFamilyVariantRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryFamilyVariantRepository();
    }

}
