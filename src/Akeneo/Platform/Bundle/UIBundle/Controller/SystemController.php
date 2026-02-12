<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Controller;

use Akeneo\Platform\Bundle\UIBundle\Query\CountSystemEntitiesQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class SystemController
{
    public function __construct(private CountSystemEntitiesQueryInterface $countSystemEntitiesQuery)
    {
    }

    public function countEntitiesAction(): JsonResponse
    {
        $entities = $this->countSystemEntitiesQuery->execute();

        return new JsonResponse($entities);
    }
}
