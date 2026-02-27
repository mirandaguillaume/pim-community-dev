<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

/**
 * Ideally, we should inject a Product Query with all the filters and not a query builder.
 * Then, this query could be executed with the service of our choice (ES, Mysql, fake).
 *
 * But the current implementation of the query builder directly
 * contains the filters and has the responsibility of executing the query.
 *
 * We have to stick with this behavior for now.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class FetchProductAndProductModelRowsParameters
{
    public function __construct(private ProductQueryBuilderInterface $productQueryBuilder, private array $attributeCodes, private string $channelCode, private string $localeCode) {}

    public function productQueryBuilder(): ProductQueryBuilderInterface
    {
        return $this->productQueryBuilder;
    }

    public function attributeCodes(): array
    {
        return $this->attributeCodes;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }
}
