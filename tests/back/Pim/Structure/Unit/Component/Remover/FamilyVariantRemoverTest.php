<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Remover;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Remover\FamilyVariantRemover;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantRemoverTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private CountEntityWithFamilyVariantInterface|MockObject $counter;
    private FamilyVariantRemover $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->counter = $this->createMock(CountEntityWithFamilyVariantInterface::class);
        $this->sut = new FamilyVariantRemover($this->objectManager, $this->eventDispatcher, $this->counter);
    }

    public function test_it_is_a_remover(): void
    {
        $this->assertInstanceOf(RemoverInterface::class, $this->sut);
    }

    public function test_it_rejects_an_object_that_is_not_a_family_variant(): void
    {
        $this->expectException(InvalidObjectException::class);

        $this->sut->remove(new \stdClass());
    }

    public function test_it_refuses_to_remove_a_family_variant_still_used_by_entities(): void
    {
        $familyVariant = $this->familyVariant('shoes_size', 42);
        $this->counter->method('belongingToFamilyVariant')->with($familyVariant)->willReturn(3);
        $this->objectManager->expects($this->never())->method('remove');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Family variant "shoes_size", could not be removed as it is used by some entities with family variants.',
        );

        $this->sut->remove($familyVariant);
    }

    public function test_it_removes_a_family_variant_used_by_no_entity_and_dispatches_events(): void
    {
        $familyVariant = $this->familyVariant('shoes_size', 42);
        $this->counter->method('belongingToFamilyVariant')->willReturn(0);
        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch')->with(
            $this->callback(fn(RemoveEvent $event): bool => $event->getSubject() === $familyVariant && 42 === $event->getSubjectId()),
            $this->logicalOr(StorageEvents::PRE_REMOVE, StorageEvents::POST_REMOVE),
        );
        $this->objectManager->expects($this->once())->method('remove')->with($familyVariant);
        $this->objectManager->expects($this->once())->method('flush');

        $result = $this->sut->remove($familyVariant);

        $this->assertSame($this->sut, $result);
    }

    private function familyVariant(string $code, int $id): FamilyVariantInterface|MockObject
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $familyVariant->method('getCode')->willReturn($code);
        $familyVariant->method('getId')->willReturn($id);

        return $familyVariant;
    }
}
