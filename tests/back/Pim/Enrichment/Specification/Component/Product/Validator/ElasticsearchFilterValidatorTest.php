<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableLocaleException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnavailableSpecificLocaleException;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ElasticsearchFilterValidatorTest extends TestCase
{
    private ElasticsearchFilterValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ElasticsearchFilterValidator();
    }

}
