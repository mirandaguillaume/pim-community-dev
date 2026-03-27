<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator\StorageHydrator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageHydratorTest extends TestCase
{
    private StorageHydratorInterface|MockObject $localHydrator;
    private StorageHydratorInterface|MockObject $noneHydrator;
    private StorageHydrator $sut;

    protected function setUp(): void
    {
        $this->localHydrator = $this->createMock(StorageHydratorInterface::class);
        $this->noneHydrator = $this->createMock(StorageHydratorInterface::class);
        $this->sut = new StorageHydrator([
            $this->noneHydrator,
            $this->localHydrator,
        ]);
        $this->localHydrator->method('supports')->willReturnCallback(function (array $data): bool {
            return ($data['type'] ?? null) === 'local';
        });
        $this->noneHydrator->method('supports')->willReturnCallback(function (array $data): bool {
            return ($data['type'] ?? null) === 'none';
        });
    }

    public function test_it_supports_hydration_when_an_hydrator_support_hydration(): void
    {
        $this->assertSame(true, $this->sut->supports(['type' => 'none']));
        $this->assertSame(false, $this->sut->supports(['type' => 'unknown']));
    }

    public function test_it_hydrates_with_the_first_supported_hydrator(): void
    {
        $this->noneHydrator->method('hydrate')->with(['type' => 'none'])->willReturn(new NoneStorage());
        $this->assertEquals(new NoneStorage(), $this->sut->hydrate(['type' => 'none']));
    }

    public function test_it_throws_an_exception_when_no_hydrator_supports_hydration(): void
    {
        $this->expectException(\LogicException::class);
        $this->sut->hydrate(['type' => 'unknown']);
    }
}
