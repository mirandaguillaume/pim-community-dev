<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\MappingsOverrideConfigurator;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

// Define real classes with inheritance for the test
class OriginalQux1
{
}
class OriginalQux2
{
}
class OverrideQux1 extends OriginalQux1
{
}
class OverrideQux2 extends OriginalQux2
{
}

class MappingsOverrideConfiguratorTest extends TestCase
{
    private EntityManagerInterface|MockObject $em;
    private Configuration|MockObject $configuration;
    private MappingsOverrideConfigurator $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->configuration = $this->createMock(Configuration::class);
        $this->sut = new MappingsOverrideConfigurator();
        $this->em->method('getConfiguration')->willReturn($this->configuration);
    }

    public function test_it_configures_the_mappings_of_an_original_model_that_is_override(): void
    {
        $metadataInfo = new ClassMetadata('Foo\Bar\OriginalQux');
        $metadataInfo->mapManyToMany(['fieldName' => 'relation1', 'targetEntity' => 'Foo']);
        $metadataInfo->mapManyToOne(['fieldName' => 'relation2', 'targetEntity' => 'Foo']);
        $metadataInfo->mapOneToMany(['fieldName' => 'relation3', 'targetEntity' => 'Foo', 'mappedBy' => 'baz']);
        $metadataInfo->mapOneToOne(['fieldName' => 'relation4', 'targetEntity' => 'Foo']);
        $overrides = [
                    ['original' => 'Foo\Bar\OriginalQux', 'override' => 'Acme\Bar\OverrideQux'],
                    ['original' => 'Foo\Baz\OriginalQux', 'override' => 'Acme\Baz\OverrideQux'],
                ];
        $this->sut->configure($metadataInfo, $overrides, $this->configuration);
        // Original model should have isMappedSuperclass set to true by the configurator
        $this->assertTrue($metadataInfo->isMappedSuperclass);
    }

    public function test_it_configures_the_mappings_of_a_model_that_overrides_an_original_model(): void
    {
        $mappingDriver = $this->createMock(MappingDriver::class);
        $namingStrategy = $this->createMock(NamingStrategy::class);

        $originalQux1 = OriginalQux1::class;
        $originalQux2 = OriginalQux2::class;
        $overrideQux1 = OverrideQux1::class;
        $overrideQux2 = OverrideQux2::class;

        $mappingDriver->method('getAllClassNames')->willReturn([$originalQux1]);
        $this->configuration->method('getMetadataDriverImpl')->willReturn($mappingDriver);
        $this->configuration->method('getNamingStrategy')->willReturn($namingStrategy);

        $metadataInfo = new ClassMetadata($overrideQux1);
        $mappingDriver->expects($this->once())->method('loadMetadataForClass')->with($originalQux1, $this->anything());

        $overrides = [
                    ['original' => $originalQux1, 'override' => $overrideQux1],
                    ['original' => $originalQux2, 'override' => $overrideQux2],
                ];
        $this->sut->configure($metadataInfo, $overrides, $this->configuration);
    }
}
