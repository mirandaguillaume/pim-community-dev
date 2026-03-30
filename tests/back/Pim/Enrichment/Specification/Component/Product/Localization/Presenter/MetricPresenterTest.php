<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\MetricPresenter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MetricPresenterTest extends TestCase
{
    private MetricPresenter $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricPresenter();
    }

}
