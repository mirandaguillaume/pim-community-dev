<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Connector\Processor\Normalization\Processor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProcessorTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private ObjectDetacherInterface|MockObject $objectDetacher;
    private Processor $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->objectDetacher = $this->createMock(ObjectDetacherInterface::class);
        $this->sut = new Processor($this->normalizer, $this->objectDetacher);
    }

    public function test_it_is_a_processor(): void
    {
        $this->assertInstanceOf(ItemProcessorInterface::class, $this->sut);
    }

    public function test_it_processes_items(): void
    {
        $group = $this->createMock(GroupInterface::class);

        $this->normalizer->expects($this->once())->method('normalize')->with($group)->willReturn([
                        'code'   => 'promotion',
                        'type'   => 'RELATED',
                        'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung'],
                    ]);
        $this->objectDetacher->expects($this->once())->method('detach')->with($group);
        $this->assertSame([
                    'code'   => 'promotion',
                    'type'   => 'RELATED',
                    'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung'],
                ], $this->sut->process($group));
    }
}
