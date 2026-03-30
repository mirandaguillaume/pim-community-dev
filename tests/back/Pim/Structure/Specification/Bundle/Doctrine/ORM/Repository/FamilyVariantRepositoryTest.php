<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyVariantRepository;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class FamilyVariantRepositoryTest extends TestCase
{
    private FamilyVariantRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantRepository();
    }

}
