<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverter;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class AttributeConverterTest extends TestCase
{
    private AttributeConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeConverter();
    }

}
