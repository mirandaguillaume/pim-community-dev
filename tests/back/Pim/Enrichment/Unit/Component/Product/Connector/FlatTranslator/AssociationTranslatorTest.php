<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AssociationTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use PHPUnit\Framework\TestCase;

class AssociationTranslatorTest extends TestCase
{
    private AssociationTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationTranslator();
    }

}
