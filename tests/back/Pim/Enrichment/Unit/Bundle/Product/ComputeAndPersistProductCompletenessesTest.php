<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Clock;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenessesTest extends TestCase
{
    private CompletenessCalculator|MockObject $completenessCalculator;
    private SaveProductCompletenesses|MockObject $saveProductCompletenesses;
    private GetProductCompletenesses|MockObject $getProductCompletenesses;
    private EventDispatcher|MockObject $eventDispatcher;
    private Clock|MockObject $clock;
    private TokenStorageInterface|MockObject $tokenStorage;
    private LoggerInterface|MockObject $logger;
    private ComputeAndPersistProductCompletenesses $sut;

    protected function setUp(): void
    {
        $this->completenessCalculator = $this->createMock(CompletenessCalculator::class);
        $this->saveProductCompletenesses = $this->createMock(SaveProductCompletenesses::class);
        $this->getProductCompletenesses = $this->createMock(GetProductCompletenesses::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->clock = $this->createMock(Clock::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new ComputeAndPersistProductCompletenesses($this->completenessCalculator,
            $this->saveProductCompletenesses,
            $this->getProductCompletenesses,
            $this->eventDispatcher,
            $this->clock,
            $this->tokenStorage,
            $this->logger);
    }

    public function test_it_can_be_initialized(): void
    {
        $this->assertInstanceOf(ComputeAndPersistProductCompletenesses::class, $this->sut);
    }

    public function test_it_dispatches_event_when_products_have_been_completed(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->getProductCompletenesses->method('fromProductUuids')->with([
                    $uuid1->toString(),
                    $uuid2->toString(),
                ])->willReturn([
                        $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                            new ProductCompleteness('ecommerce', 'fr_FR', 10, 6),
                            new ProductCompleteness('ecommerce', 'en_US', 10, 0),
                        ]),
                        $uuid2->toString() => new ProductCompletenessCollection($uuid2, [
                            new ProductCompleteness('mobile', 'fr_FR', 10, 8),
                            new ProductCompleteness('ecommerce', 'en_US', 10, 1),
                        ]),
                    ]);
        $newProductsCompletenesses = [
                    $uuid1->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid1->toString(), [
                        new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 10, []),
                        new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
                    ]),
                    $uuid2->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid2->toString(), [
                        new ProductCompletenessWithMissingAttributeCodes('mobile', 'fr_FR', 10, ['name', 'title', 'short_title', 'weight', 'length']),
                        new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
                    ]),
                ];
        $this->completenessCalculator->expects($this->once())->method('fromProductUuids')->with([$uuid1->toString(), $uuid2->toString()])->willReturn($newProductsCompletenesses);
        $this->saveProductCompletenesses->expects($this->once())->method('saveAll')->with($newProductsCompletenesses);
        $changedAt = new \DateTimeImmutable('2022-10-01');
        $this->clock->method('now')->willReturn($changedAt);
        $event = new ProductWasCompletedOnChannelLocaleCollection([
                    new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid($uuid1), $changedAt, 'ecommerce', 'fr_FR', '1'),
                    new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid($uuid2), $changedAt, 'ecommerce', 'en_US', '1'),
                ]);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event);
        $this->sut->fromProductUuids([
                    $uuid1->toString(),
                    $uuid2->toString(),
                ]);
    }

    public function test_it_doesnt_dispatch_event_when_products_have_not_been_completed(): void
    {
        $uuid1 = Uuid::uuid4();
        $this->getProductCompletenesses->method('fromProductUuids')->with([
                    $uuid1->toString(),
                ])->willReturn([
                        $uuid1->toString() => new ProductCompletenessCollection($uuid1, [
                            new ProductCompleteness('ecommerce', 'fr_FR', 10, 2),
                        ]),
                    ]);
        $newProductsCompletenesses = [
                    $uuid1->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($uuid1->toString(), [
                        new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 10, ['name', 'title']),
                    ]),
                ];
        $this->completenessCalculator->method('fromProductUuids')->with([$uuid1->toString()])->willReturn($newProductsCompletenesses);
        $this->saveProductCompletenesses->expects($this->once())->method('saveAll')->with($newProductsCompletenesses);
        $changedAt = new \DateTimeImmutable('2022-10-01');
        $this->clock->method('now')->willReturn($changedAt);
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->anything());
        $this->sut->fromProductUuids([$uuid1->toString()]);
    }
}
