<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Storage\Sql;

use Akeneo\Pim\Structure\Bundle\Storage\Sql\FamilyVariantsByAttributeAxes;
use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class FamilyVariantsByAttributeAxesTest extends TestCase
{
    private FamilyVariantsByAttributeAxes $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantsByAttributeAxes();
    }

}
