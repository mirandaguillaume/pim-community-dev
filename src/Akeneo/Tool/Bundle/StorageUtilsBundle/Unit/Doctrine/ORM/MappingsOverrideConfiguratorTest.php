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
        $metadataInfo = $this->createMock(ClassMetadata::class);
        $mappingDriver = $this->createMock(MappingDriver::class);
        $namingStrategy = $this->createMock(NamingStrategy::class);

        $originalQux1 = __NAMESPACE__ . '\OriginalQux1';
        $originalQux2 = __NAMESPACE__ . '\OriginalQux2';
        $overrideQux1 = __NAMESPACE__ . '\OverrideQux1';
        $overrideQux2 = __NAMESPACE__ . '\OverrideQux2';
        $mappingDriver->method('getAllClassNames')->willReturn([$originalQux1]);
        $this->configuration->method('getMetadataDriverImpl')->willReturn($mappingDriver);
        $this->configuration->method('getNamingStrategy')->willReturn($namingStrategy);
        $metadataInfo->method('getName')->willReturn($overrideQux1);
        $mappingDriver->expects($this->once())->method('loadMetadataForClass')->with($originalQux1, $this->anything());
        $overrides = [
                    ['original' => $originalQux1, 'override' => $overrideQux1],
                    ['original' => $originalQux2, 'override' => $overrideQux2],
                ];
        $this->sut->configure($metadataInfo, $overrides, $this->configuration);
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}
