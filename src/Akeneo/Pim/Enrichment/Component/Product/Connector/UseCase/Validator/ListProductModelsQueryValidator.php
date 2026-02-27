<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ListProductModelsQueryValidator
{
    public function __construct(private readonly ValidateAttributes $validateAttributes, private readonly ValidateChannel $validateChannel, private readonly ValidateLocales $validateLocales, private readonly ValidatePagination $validatePagination, private readonly ValidateCriterion $validateCriterion, private readonly ValidateCategories $validateCategories, private readonly ValidateProperties $validateProperties, private readonly ValidateSearchLocale $validateSearchLocales, private readonly ValidateGrantedSearchLocaleInterface $validateGrantedSearchLocales, private readonly ValidateGrantedCategoriesInterface $validateGrantedCategories, private readonly ValidateGrantedPropertiesInterface $validateGrantedProperties, private readonly ValidateGrantedAttributesInterface $validateGrantedAttributes, private readonly ValidateGrantedLocalesInterface $validateGrantedLocales)
    {
    }

    /**
     * @throws InvalidQueryException
     */
    public function validate(ListProductModelsQuery $query): void
    {
        $this->validatePagination->validate(
            $query->paginationType,
            $query->page,
            $query->limit,
            $query->withCount
        );
        $this->validateAttributes->validate($query->attributeCodes);
        $this->validateGrantedAttributes->validate($query->attributeCodes);
        $this->validateChannel->validate($query->channelCode);
        $this->validateLocales->validate($query->localeCodes, $query->channelCode);
        $this->validateCriterion->validate($query->search);
        $this->validateCategories->validate($query->search);
        $this->validateGrantedCategories->validate($query->search);
        $this->validateProperties->validate($query->search);
        $this->validateGrantedProperties->validate($query->search);
        $this->validateSearchLocales->validate($query->search, $query->searchLocaleCode);
        $this->validateGrantedLocales->validateForLocaleCodes($query->localeCodes);
        $this->validateGrantedSearchLocales->validate($query->search, $query->searchLocaleCode);
    }
}
