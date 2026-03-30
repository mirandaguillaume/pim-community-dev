<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductModelsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedCategories;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedLocales;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedProperties;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAlwaysGrantedSearchLocale;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCategories;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateChannel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCriterion;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateLocales;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidatePagination;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateProperties;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateSearchLocale;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\Api\Pagination\PaginationParametersValidator;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListProductModelsQueryValidatorTest extends TestCase
{
    private ListProductModelsQueryValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ListProductModelsQueryValidator();
    }

}
