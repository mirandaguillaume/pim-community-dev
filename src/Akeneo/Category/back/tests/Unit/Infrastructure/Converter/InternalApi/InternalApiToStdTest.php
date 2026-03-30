<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\Checker\InternalApiRequirementChecker;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InternalApiToStdTest extends TestCase
{
    private InternalApiRequirementChecker|MockObject $checker;
    private InternalApiToStd $sut;

    protected function setUp(): void
    {
        $this->checker = $this->createMock(InternalApiRequirementChecker::class);
        $this->sut = new InternalApiToStd($this->checker);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(InternalApiToStd::class, $this->sut);
        $this->assertInstanceOf(ConverterInterface::class, $this->sut);
    }

    public function testItConverts(): void
    {
        $data = [
            'id' => 1,
            'properties' => [
                'code' => 'mycode',
                'labels' => [
                    'fr_FR' => 'Chaussettes',
                    'en_US' => 'Socks',
                ],
            ],
            'attributes' => [
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => 'Les chaussures dont vous avez besoin !',
                    'locale' => 'fr_FR',
                    'attribute_code' => 'title_87939c45-1d85-4134-9579-d594fff65030',
                ],
            ],
        ];
        $expected = [
            'id' => 1,
            'code' => 'mycode',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks',
            ],
            'values' => [
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => 'Les chaussures dont vous avez besoin !',
                    'locale' => 'fr_FR',
                    'attribute_code' => 'title_87939c45-1d85-4134-9579-d594fff65030',
                ],
            ],
        ];
        $this->checker->expects($this->once())->method('check')->with($data);
        $this->assertSame($expected, $this->sut->convert($data));
    }
}
