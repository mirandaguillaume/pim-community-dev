<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCategories;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ValidateCategoriesTest extends TestCase
{
    private ValidateCategories $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateCategories();
    }

}
