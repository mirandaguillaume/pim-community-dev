<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductXlsxExport;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PHPUnit\Framework\TestCase;

class ProductXlsxExportTest extends TestCase
{
    private ProductXlsxExport $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductXlsxExport();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}
