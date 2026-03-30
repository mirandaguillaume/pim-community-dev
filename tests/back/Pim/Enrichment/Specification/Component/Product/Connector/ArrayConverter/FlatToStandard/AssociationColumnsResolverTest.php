<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class AssociationColumnsResolverTest extends TestCase
{
    private AssociationColumnsResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationColumnsResolver();
    }

}
