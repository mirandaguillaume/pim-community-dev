<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class IdentifierResultTest extends TestCase
{
    private IdentifierResult $sut;

    protected function setUp(): void
    {
        $this->sut = new IdentifierResult();
    }

}
