<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\GetCaseSensitiveLocaleCodeInterface;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedLocalesInterface;
use Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\AttributeOption;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PHPUnit\Framework\TestCase;

class AttributeOptionTest extends TestCase
{
    private AttributeOption $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOption();
    }

}
