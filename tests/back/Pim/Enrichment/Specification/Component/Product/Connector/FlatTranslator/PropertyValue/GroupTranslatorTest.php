<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\GroupTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use PHPUnit\Framework\TestCase;

class GroupTranslatorTest extends TestCase
{
    private GroupTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupTranslator();
    }

}
