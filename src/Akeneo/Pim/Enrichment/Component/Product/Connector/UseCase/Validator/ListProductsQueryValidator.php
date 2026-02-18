<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class ListProductsQueryValidator
{
    public function __construct(private ValidateAttributes $validateAttributes, private ValidateChannel $validateChannel, private ValidateLocales $validateLocales, private ValidatePagination $validatePagination, private ValidateCriterion $validateCriterion, private ValidateCategories $validateCategories, private ValidateProperties $validateProperties, private ValidateSearchLocale $validateSearchLocales, private ValidateGrantedSearchLocaleInterface $validateGrantedSearchLocales, private ValidateGrantedCategoriesInterface $validateGrantedCategories, private ValidateGrantedPropertiesInterface $validateGrantedProperties, private ValidateGrantedAttributesInterface $validateGrantedAttributes, private ValidateGrantedLocalesInterface $validateGrantedLocales, private ValidateIdentifiersLimit $validateIdentifiersLimit)
    {
    }

    /**
     * @throws InvalidQueryException
     */
    public function validate(ListProductsQuery $query): void
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
        $this->validateIdentifiersLimit->validate($query->search);
        $this->validateGrantedProperties->validate($query->search);
        $this->validateSearchLocales->validate($query->search, $query->searchLocaleCode);
        $this->validateGrantedLocales->validateForLocaleCodes($query->localeCodes);
        $this->validateGrantedSearchLocales->validate($query->search, $query->searchLocaleCode);
    }
}
