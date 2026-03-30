<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\AssociationTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PHPUnit\Framework\TestCase;

class AssociationTranslatorTest extends TestCase
{
    private AssociationTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationTranslator();
    }

}
