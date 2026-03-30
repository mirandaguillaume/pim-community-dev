<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Category\Infrastructure\Component\Connector\ArrayConverter\StandardToFlat\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $sut;

    protected function setUp(): void
    {
        $this->sut = new Category();
    }

    public function testItConvertsFromStandardToFlatFormat(): void
    {
        $expected = [
            'code' => 'armors',
            'parent' => '',
            'label-fr_FR' => 'Armures',
            'label-en_US' => 'Armors',
        ];
        $item = [
            'code' => 'armors',
            'parent' => null,
            'labels' => [
                'fr_FR' => 'Armures',
                'en_US' => 'Armors',
            ],
        ];
        $this->assertSame($expected, $this->sut->convert($item));
    }
}
