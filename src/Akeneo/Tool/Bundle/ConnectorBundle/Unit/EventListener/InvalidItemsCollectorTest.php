<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector;

class InvalidItemsCollectorTest extends TestCase
{
    private InvalidItemsCollector $sut;

    protected function setUp(): void
    {
        $this->sut = new InvalidItemsCollector();
    }

    public function test_it_collects_invalid_items_from_event(): void
    {
        $event = $this->createMock(InvalidItemEvent::class);
        $invalidItem = $this->createMock(DataInvalidItem::class);

        $item = [
                    'sku'        => 'sku-001',
                    'name_en-us' => 'Black shoes',
                    'name_fr-fr' => 'Chaussures noires',
                ];
        $event->method('getItem')->willReturn($invalidItem);
        $invalidItem->method('getInvalidData')->willReturn($item);
        $hashKey = md5(serialize($item));
        $this->sut->collect($event);
        $this->assertSame([$hashKey => $invalidItem], $this->sut->getInvalidItems());
    }

    public function test_it_collects_several_invalid_items_from_events(): void
    {
        $event1 = $this->createMock(InvalidItemEvent::class);
        $event2 = $this->createMock(InvalidItemEvent::class);
        $event3 = $this->createMock(InvalidItemEvent::class);
        $invalidItem1 = $this->createMock(DataInvalidItem::class);
        $invalidItem2 = $this->createMock(DataInvalidItem::class);
        $invalidItem3 = $this->createMock(DataInvalidItem::class);

        $item1 = [
                    'sku'        => 'sku-001',
                    'name_en-us' => 'Black shoes',
                    'name_fr-fr' => 'Chaussures noires',
                ];
        $item2 = [
                    'sku'        => 'sku-002',
                    'name_en-us' => 'Pink shoes',
                    'name_fr-fr' => 'Chaussures roses',
                ];
        $item3 = [
                    'sku'        => 'sku-004',
                    'name_en-us' => 'Yellow shoes',
                    'name_fr-fr' => 'Chaussures jaunes',
                ];
        $event1->method('getItem')->willReturn($invalidItem1);
        $event2->method('getItem')->willReturn($invalidItem2);
        $event3->method('getItem')->willReturn($invalidItem3);
        $invalidItem1->method('getInvalidData')->willReturn($item1);
        $invalidItem2->method('getInvalidData')->willReturn($item2);
        $invalidItem3->method('getInvalidData')->willReturn($item3);
        $hashKeyItem1 = md5(serialize($item1));
        $hashKeyItem2 = md5(serialize($item2));
        $hashKeyItem3 = md5(serialize($item3));
        $this->sut->collect($event1);
        $this->sut->collect($event2);
        $this->sut->collect($event3);
        $this->assertSame([
                    $hashKeyItem1 => $invalidItem1,
                    $hashKeyItem2 => $invalidItem2,
                    $hashKeyItem3 => $invalidItem3,
                ], $this->sut->getInvalidItems());
    }

    public function test_it_does_not_collect_duplicate_invalid_items(): void
    {
        $event = $this->createMock(InvalidItemEvent::class);
        $invalidItem = $this->createMock(FileInvalidItem::class);

        $event->method('getItem')->willReturn($invalidItem);
        $invalidItem->method('getItemPosition')->willReturn(3);
        $hashKeyItem = md5(serialize(['position' => 3]));
        $this->sut->collect($event);
        $this->assertSame([$hashKeyItem => $invalidItem], $this->sut->getInvalidItems());
    }
}
