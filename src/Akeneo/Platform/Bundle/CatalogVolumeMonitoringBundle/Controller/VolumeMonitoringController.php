<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VolumeMonitoringController
{
    public function __construct(private readonly Normalizer\Volumes $volumesNormalizer, private readonly SecurityFacade $securityFacade)
    {
    }

    public function getVolumesAction(): Response
    {
        if (!$this->securityFacade->isGranted('view_catalog_volume_monitoring')) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->volumesNormalizer->volumes());
    }
}
