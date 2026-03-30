<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\PriceLocalizer;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceLocalizerTest extends TestCase
{
    private PriceLocalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceLocalizer();
    }

}
