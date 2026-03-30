<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AddVersionListenerTest extends TestCase
{
    private VersionManager|MockObject $versionManager;
    private NormalizerInterface|MockObject $versioningNormalizer;
    private UpdateGuesserInterface|MockObject $updateGuesser;
    private VersionContext|MockObject $versionContext;
    private AddVersionListener $sut;

    protected function setUp(): void
    {
        $this->versionManager = $this->createMock(VersionManager::class);
        $this->versioningNormalizer = $this->createMock(NormalizerInterface::class);
        $this->updateGuesser = $this->createMock(UpdateGuesserInterface::class);
        $this->versionContext = $this->createMock(VersionContext::class);
        $this->sut = new AddVersionListener($this->versionManager, $this->versioningNormalizer, $this->updateGuesser, $this->versionContext);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddVersionListener::class, $this->sut);
    }
}
