<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ClearerRegistryInterface
{
    /**
     * Register a clearer in the registry.
     */
    public function register(ClearerInterface $clearer): void;

    /**
     * Get a clearer compatible with given property.
     */
    public function getClearer(string $property): ?ClearerInterface;
}
