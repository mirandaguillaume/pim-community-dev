<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\CleanLineBreaksInTextAttributes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\TestCase;

class CleanLineBreaksInTextAttributesTest extends TestCase
{
    private CleanLineBreaksInTextAttributes $sut;

    protected function setUp(): void
    {
        $this->sut = new CleanLineBreaksInTextAttributes();
    }

    private function buildAttribute(string $code, string $type): Attribute
    {
            return new Attribute(
                $code,
                $type,
                [],
                true,
                true,
                null,
                null,
                null,
                '',
                []
            );
        }
}
