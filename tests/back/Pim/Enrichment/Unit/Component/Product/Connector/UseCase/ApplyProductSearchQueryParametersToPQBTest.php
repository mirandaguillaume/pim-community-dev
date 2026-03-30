<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ApplyProductSearchQueryParametersToPQBTest extends TestCase
{
    private ApplyProductSearchQueryParametersToPQB $sut;

    protected function setUp(): void
    {
        $this->sut = new ApplyProductSearchQueryParametersToPQB();
    }

}
