<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\BooleanValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\IdentifierValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\NumberValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\TextAreaValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\TextValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReadValueCollectionFactoryTest extends TestCase
{
    private ReadValueCollectionFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ReadValueCollectionFactory();
    }

}
