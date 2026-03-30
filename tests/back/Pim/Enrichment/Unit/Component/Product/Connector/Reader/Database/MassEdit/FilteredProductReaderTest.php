<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductReader;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PHPUnit\Framework\TestCase;

class FilteredProductReaderTest extends TestCase
{
    private FilteredProductReader $sut;

    protected function setUp(): void
    {
        $this->sut = new FilteredProductReader();
    }

}
