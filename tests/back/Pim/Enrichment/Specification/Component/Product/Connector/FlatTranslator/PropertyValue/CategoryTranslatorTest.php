<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\CategoryTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use PHPUnit\Framework\TestCase;

class CategoryTranslatorTest extends TestCase
{
    private CategoryTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoryTranslator();
    }

}
