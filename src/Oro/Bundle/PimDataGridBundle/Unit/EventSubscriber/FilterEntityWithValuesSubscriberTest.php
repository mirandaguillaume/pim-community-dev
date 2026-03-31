<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriberConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterEntityWithValuesSubscriberTest extends TestCase
{
    private FilterEntityWithValuesSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new FilterEntityWithValuesSubscriber();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FilterEntityWithValuesSubscriber::class, $this->sut);
    }

    public function test_it_does_not_filter_non_entity_with_values_object(): void
    {
        $entity = new \stdClass();
        $event = $this->createMock(LifecycleEventArgs::class);

        $event->method('getObject')->willReturn($entity);
        $this->sut->postLoad($event);
    }

    public function test_it_does_not_filter_entity_with_values_by_default(): void
    {
        $entity = $this->createMock(EntityWithValuesInterface::class);
        $event = $this->createMock(LifecycleEventArgs::class);

        $entity->expects($this->never())->method('setRawValues');
        $event->method('getObject')->willReturn($entity);
        $this->sut->postLoad($event);
    }

    public function test_it_does_not_filter_entity_with_values_when_filtering_not_activated(): void
    {
        $entity = $this->createMock(EntityWithValuesInterface::class);
        $event = $this->createMock(LifecycleEventArgs::class);

        $this->sut->configure(FilterEntityWithValuesSubscriberConfiguration::doNotFilterEntityValues());
        $entity->expects($this->never())->method('setRawValues');
        $event->method('getObject')->willReturn($entity);
        $this->sut->postLoad($event);
    }

    public function test_it_filters_raw_values_when_filtering_activated(): void
    {
        $entity = $this->createMock(EntityWithValuesInterface::class);
        $event = $this->createMock(LifecycleEventArgs::class);

        $this->sut->configure(FilterEntityWithValuesSubscriberConfiguration::filterEntityValues(['attribute_1', 'attribute_3']));
        $entity->method('getRawValues')->willReturn([
                    'attribute_1' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_2' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_3' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                ]);
        $entity->expects($this->once())->method('setRawValues')->with([
                    'attribute_1' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_3' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                ]);
        $event->method('getObject')->willReturn($entity);
        $this->sut->postLoad($event);
    }

    public function test_it_filters_by_keeping_keeps_attribute_as_label_and_image_for_family_entity(): void
    {
        $entity = $this->createMock(EntityWithFamilyInterface::class);
        $event = $this->createMock(LifecycleEventArgs::class);
        $family = $this->createMock(FamilyInterface::class);
        $attributeAsImage = $this->createMock(AttributeInterface::class);
        $attributeAsLabel = $this->createMock(AttributeInterface::class);

        $this->sut->configure(FilterEntityWithValuesSubscriberConfiguration::filterEntityValues(['attribute_1', 'attribute_3']));
        $entity->method('getRawValues')->willReturn([
                    'attribute_1' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_2' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_3' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_as_label' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_as_image' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                ]);
        $entity->expects($this->once())->method('setRawValues')->with([
                    'attribute_1' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_3' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_as_label' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'attribute_as_image' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                ]);
        $entity->method('getFamily')->willReturn($family);
        $family->method('getAttributeAsImage')->willReturn($attributeAsImage);
        $family->method('getAttributeAsLabel')->willReturn($attributeAsLabel);
        $attributeAsImage->method('getCode')->willReturn('attribute_as_image');
        $attributeAsLabel->method('getCode')->willReturn('attribute_as_label');
        $event->method('getObject')->willReturn($entity);
        $this->sut->postLoad($event);
    }
}
