<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateSearchLocale;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ValidateSearchLocaleTest extends TestCase
{
    private ValidateSearchLocale $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateSearchLocale();
    }

}
