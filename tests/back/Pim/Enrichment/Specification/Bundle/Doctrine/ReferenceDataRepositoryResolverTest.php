<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class ReferenceDataRepositoryResolverTest extends TestCase
{
    private ReferenceDataRepositoryResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataRepositoryResolver();
    }

}
