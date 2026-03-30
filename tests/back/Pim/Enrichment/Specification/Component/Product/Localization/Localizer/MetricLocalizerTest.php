<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetricLocalizerTest extends TestCase
{
    private MetricLocalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricLocalizer();
    }

}
