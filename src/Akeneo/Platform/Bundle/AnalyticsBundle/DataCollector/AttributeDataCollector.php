<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * Collect data about attributes:
 *  - number of scopable attribute
 *  - number of localizable attribute
 *  - number of localizable and scopable attribute
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDataCollector implements DataCollectorInterface
{
    public function __construct(private readonly CountQuery $attributeCountQuery, private readonly CountQuery $localizableAttributeCountQuery, private readonly CountQuery $scopableAttributeCountQuery, private readonly CountQuery $localizableAndScopableAttributeCountQuery, private readonly CountQuery $useableAsGridFilterAttributeCountQuery, private readonly AverageMaxQuery $localizableAttributePerFamilyAverageMaxQuery, private readonly AverageMaxQuery $scopableAttributePerFamilyAverageMaxQuery, private readonly AverageMaxQuery $localizableAndScopableAttributePerFamilyAverageMaxQuery, private readonly AverageMaxQuery $attributePerFamilyAverageMaxQuery)
    {
    }

    public function collect(): array
    {
        $data = [
            'nb_attributes' => $this->attributeCountQuery->fetch()->getVolume(),
            'nb_scopable_attributes' => $this->scopableAttributeCountQuery->fetch()->getVolume(),
            'nb_localizable_attributes' => $this->localizableAttributeCountQuery->fetch()->getVolume(),
            'nb_scopable_localizable_attributes' => $this->localizableAndScopableAttributeCountQuery->fetch()->getVolume(),
            'nb_useable_as_grid_filter_attributes' => $this->useableAsGridFilterAttributeCountQuery->fetch()->getVolume(),
            'avg_percentage_scopable_attributes_per_family' => $this->scopableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_percentage_localizable_attributes_per_family' => $this->localizableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_percentage_scopable_localizable_attributes_per_family' => $this->localizableAndScopableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_number_attributes_per_family' => $this->attributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
        ];

        return $data;
    }
}
