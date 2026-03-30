<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Manager;

use Akeneo\Pim\Structure\Component\Manager\AttributeOptionsSorter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeOptionsSorterTest extends TestCase
{
    private BulkSaverInterface|MockObject $optionSaver;
    private AttributeOptionsSorter $sut;

    protected function setUp(): void
    {
        $this->optionSaver = $this->createMock(BulkSaverInterface::class);
        $this->sut = new AttributeOptionsSorter($this->optionSaver);
    }

    public function test_it_sorts_options(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $size = $this->createMock(AttributeOption::class);
        $width = $this->createMock(AttributeOption::class);

        $size->method('getId')->willReturn(45);
        $siteValue = (new AttributeOptionValue())->setLocale('en_US')->setValue('big');
        $size->method('addOptionValue')->with($siteValue);
        $width->method('getId')->willReturn(18);
        $widthValue = (new AttributeOptionValue())->setLocale('en_US')->setValue('wide');
        $width->method('addOptionValue')->with($widthValue);
        $attribute->method('getOptions')->willReturn([$size, $width]);
        $size->expects($this->once())->method('setSortOrder')->with(2)->willReturn($size);
        $width->expects($this->once())->method('setSortOrder')->with(1)->willReturn($width);
        $this->optionSaver->expects($this->once())->method('saveAll')->with([0 => $size, 1 => $width]);
        $this->sut->updateSorting($attribute, [18 => 1, 45 => 2]);
    }
}
