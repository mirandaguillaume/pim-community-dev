<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\AttributeTranslator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Channel\GetChannelTranslations;
use Akeneo\Tool\Component\Localization\CurrencyTranslator;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use Akeneo\Tool\Component\Localization\LanguageTranslator;
use PHPUnit\Framework\TestCase;

class AttributeTranslatorTest extends TestCase
{
    private AttributeTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeTranslator();
    }

}
