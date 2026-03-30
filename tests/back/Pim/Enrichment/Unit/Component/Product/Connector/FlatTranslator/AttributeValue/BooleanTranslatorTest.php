<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\BooleanTranslator;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PHPUnit\Framework\TestCase;

class BooleanTranslatorTest extends TestCase
{
    private BooleanTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanTranslator();
    }

}
