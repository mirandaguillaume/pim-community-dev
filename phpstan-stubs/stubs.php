<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category {
    class GetGrantedCategoryCodes
    {
        /**
         * @param int[] $groupIds
         * @return string[]
         */
        public function forGroupIds(array $groupIds): array
        {
            return [];
        }
    }
}

namespace Akeneo\Platform\TailoredImport\Test\Integration {
    class ControllerIntegrationTestCase extends \PHPUnit\Framework\TestCase
    {
        /** @var mixed */
        protected $client;
        /** @var mixed */
        protected $webClientHelper;
        /** @var mixed */
        protected $catalog;

        public function get(string $service)
        {
            return null;
        }
    }
}
