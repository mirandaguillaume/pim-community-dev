<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\CatalogVolumeMonitoringBundle\Controller;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Controller\VolumeMonitoringController;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer\Volumes;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class VolumeMonitoringControllerTest extends TestCase
{
    private Volumes|MockObject $volumesNormalizer;
    private SecurityFacade|MockObject $securityFacade;
    private VolumeMonitoringController $sut;

    protected function setUp(): void
    {
        $this->volumesNormalizer = $this->createMock(Volumes::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->sut = new VolumeMonitoringController($this->volumesNormalizer, $this->securityFacade);
    }

    public function test_it_throws_an_exception_if_the_user_is_not_granted_to_view_the_catalog_volume_monitoring(): void
    {
        $this->securityFacade->method('isGranted')->with('view_catalog_volume_monitoring')->willReturn(false);
        $this->expectException(AccessDeniedException::class);
        $this->sut->getVolumesAction();
    }
}
