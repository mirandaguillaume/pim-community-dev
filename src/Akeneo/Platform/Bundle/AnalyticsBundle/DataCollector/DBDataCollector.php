<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Tool\Component\Analytics\ActiveEventSubscriptionCountQuery;
use Akeneo\Tool\Component\Analytics\ApiConnectionCountQuery;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\Tool\Component\Analytics\EmailDomainsQuery;
use Akeneo\Tool\Component\Analytics\GetConnectedAppsIdentifiersQueryInterface;
use Akeneo\Tool\Component\Analytics\IsDemoCatalogQuery;
use Akeneo\Tool\Component\Analytics\MediaCountQuery;

/**
 * It collect data about the volume of different axes in the PIM catalog.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DBDataCollector implements DataCollectorInterface
{
    public function __construct(private readonly CountQuery $channelCountQuery, private readonly CountQuery $productCountQuery, private readonly CountQuery $localeCountQuery, private readonly CountQuery $familyCountQuery, private readonly CountQuery $attributeCountQuery, private readonly CountQuery $userCountQuery, private readonly CountQuery $productModelCountQuery, private readonly CountQuery $variantProductCountQuery, private readonly CountQuery $categoryCountQuery, private readonly CountQuery $categoryTreeCountQuery, private readonly AverageMaxQuery $categoriesInOneCategoryAverageMax, private readonly AverageMaxQuery $categoryLevelsAverageMax, private readonly CountQuery $productValueCountQuery, private readonly AverageMaxQuery $productValueAverageMaxQuery, private readonly AverageMaxQuery $productValuePerFamilyAverageMaxQuery, private readonly EmailDomainsQuery $emailDomains, private readonly ApiConnectionCountQuery $apiConnectionCountQuery, private readonly MediaCountQuery $mediaCountQuery, private readonly IsDemoCatalogQuery $isDemoCatalogQuery, private readonly ActiveEventSubscriptionCountQuery $activeEventSubscriptionCountQuery, private readonly GetConnectedAppsIdentifiersQueryInterface $getConnectedAppsIdentifiersQuery) {}

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $activatedAppIds = $this->getConnectedAppsIdentifiersQuery->execute();

        return [
            'nb_channels' => $this->channelCountQuery->fetch()->getVolume(),
            'nb_locales' => $this->localeCountQuery->fetch()->getVolume(),
            'nb_products' => $this->productCountQuery->fetch()->getVolume(),
            'nb_product_models' => $this->productModelCountQuery->fetch()->getVolume(),
            'nb_variant_products' => $this->variantProductCountQuery->fetch()->getVolume(),
            'nb_families' => $this->familyCountQuery->fetch()->getVolume(),
            'nb_attributes' => $this->attributeCountQuery->fetch()->getVolume(),
            'nb_users' => $this->userCountQuery->fetch()->getVolume(),
            'nb_categories' => $this->categoryCountQuery->fetch()->getVolume(),
            'nb_category_trees' => $this->categoryTreeCountQuery->fetch()->getVolume(),
            'max_category_in_one_category' => $this->categoriesInOneCategoryAverageMax->fetch()->getMaxVolume(),
            'max_category_levels' => $this->categoryLevelsAverageMax->fetch()->getMaxVolume(),
            'nb_product_values' => $this->productValueCountQuery->fetch()->getVolume(),
            'avg_product_values_by_product' => $this->productValueAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_product_values_by_family' => $this->productValuePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'max_product_values_by_family' => $this->productValuePerFamilyAverageMaxQuery->fetch()->getMaxVolume(),
            'email_domains' => $this->emailDomains->fetch(),
            'api_connection' => $this->apiConnectionCountQuery->fetch(),
            'nb_media_files_in_products' => $this->mediaCountQuery->countFiles(),
            'nb_media_images_in_products' => $this->mediaCountQuery->countImages(),
            'is_demo_catalog' => $this->isDemoCatalogQuery->fetch(),
            'nb_active_event_subscription' => $this->activeEventSubscriptionCountQuery->fetch(),
            'activated_app_ids' => $activatedAppIds,
            'nb_activated_apps' => count($activatedAppIds),
        ];
    }
}
