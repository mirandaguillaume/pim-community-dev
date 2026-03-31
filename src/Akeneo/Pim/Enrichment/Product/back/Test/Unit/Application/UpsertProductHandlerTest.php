<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Pim\Enrichment\Product\Domain\Clock;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpsertProductHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private ProductRepositoryInterface|MockObject $productRepository;
    private ProductBuilderInterface|MockObject $productBuilder;
    private SaverInterface|MockObject $productSaver;
    private ValidatorInterface|MockObject $productValidator;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private UserIntentApplierRegistry|MockObject $applierRegistry;
    private TokenStorageInterface|MockObject $tokenStorage;
    private Clock|MockObject $clock;
    private UpsertProductHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productBuilder = $this->createMock(ProductBuilderInterface::class);
        $this->productSaver = $this->createMock(SaverInterface::class);
        $this->productValidator = $this->createMock(ValidatorInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->applierRegistry = $this->createMock(UserIntentApplierRegistry::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->clock = $this->createMock(Clock::class);
        $this->sut = new UpsertProductHandler(
            $this->validator,
            $this->productRepository,
            $this->productBuilder,
            $this->productSaver,
            $this->productValidator,
            $this->eventDispatcher,
            $this->applierRegistry,
            $this->tokenStorage,
            $this->clock
        );
        $this->clock->method('now')->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
    }

    public function test_it_is_intializable(): void
    {
        $this->assertInstanceOf(UpsertProductHandler::class, $this->sut);
    }

    public function test_it_creates_updates_and_saves_a_product(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn(null);
        $this->productBuilder->expects($this->once())->method('createProduct')->with('identifier1')->willReturn($product);
        $this->productValidator->expects($this->once())->method('validate')->with($product)->willReturn(new ConstraintViolationList());
        $this->productSaver->expects($this->once())->method('save')->with($product);
        $event = new ProductWasCreated($product->getUuid(), \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event)->willReturn($event);
        $this->sut->__invoke($command);
    }

    public function test_it_fetches_updates_and_saves_a_product(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $product->setCreated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->setUpdated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn($product);
        $this->productBuilder->expects($this->never())->method('createProduct')->with('identifier1');
        $this->productValidator->expects($this->once())->method('validate')->with($product)->willReturn(new ConstraintViolationList());
        $this->productSaver->expects($this->once())->method('save')->with($product);
        $event = new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event)->willReturn($event);
        $this->sut->__invoke($command);
    }

    public function test_it_does_not_dispatch_event_when_product_was_not_updated(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product->method('getIdentifier')->willReturn('identifier1');
        $product->method('getCreated')->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->method('isDirty')->willReturn(false);
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn($product);
        $this->productBuilder->expects($this->never())->method('createProduct')->with('identifier1');
        $this->productValidator->expects($this->once())->method('validate')->with($product)->willReturn(new ConstraintViolationList());
        $this->productSaver->expects($this->once())->method('save')->with($product);
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->isInstanceOf(ProductWasCreated::class));
        $this->eventDispatcher->expects($this->never())->method('dispatch')->with($this->isInstanceOf(ProductWasUpdated::class));
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_exception_when_command_is_not_valid(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $violations = new ConstraintViolationList([
                    new ConstraintViolation('error', null, [], $command, null, null),
                ]);
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn($violations);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productSaver->expects($this->never())->method('save')->with($product);
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_exception_when_product_is_not_valid(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $violations = new ConstraintViolationList([
                    new ConstraintViolation('error', null, [], $command, null, null),
                ]);
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn($product);
        $this->productValidator->expects($this->once())->method('validate')->with($product)->willReturn($violations);
        $this->productSaver->expects($this->never())->method('save')->with($product);
        $this->expectException(LegacyViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_exception_when_updater_throws_an_exception(): void
    {
        $userIntentApplier = $this->createMock(UserIntentApplier::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $setTextUserIntent = new SetTextValue('name', null, null, 'foo');
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: [$setTextUserIntent]);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn($product);
        $this->applierRegistry->method('getApplier')->with($setTextUserIntent)->willReturn($userIntentApplier);
        $userIntentApplier->method('apply')->with($setTextUserIntent, $product, 1)->willThrowException(InvalidPropertyException::expected('error', 'class'));
        $this->productValidator->expects($this->never())->method('validate')->with($product);
        $this->productSaver->expects($this->never())->method('save')->with($product);
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_updates_a_product_with_user_intents(): void
    {
        $applier = $this->createMock(UserIntentApplier::class);
        $applier2 = $this->createMock(UserIntentApplier::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $userIntent = new SetEnabled(true);
        $setTextUserIntent = new SetTextValue('name', null, null, 'Lorem Ipsum');
        $command = UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: [$userIntent, $setTextUserIntent]);
        $product = new Product();
        $product->setCreated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->setUpdated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn($product);
        $this->applierRegistry->expects($this->once())->method('getApplier')->with($userIntent)->willReturn($applier);
        $this->applierRegistry->expects($this->once())->method('getApplier')->with($setTextUserIntent)->willReturn($applier2);
        $applier->expects($this->once())->method('apply')->with($userIntent, $product, 1);
        $applier2->expects($this->once())->method('apply')->with($setTextUserIntent, $product, 1);
        $this->productValidator->expects($this->once())->method('validate')->with($product)->willReturn(new ConstraintViolationList());
        $this->productSaver->expects($this->once())->method('save')->with($product);
        $event = new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event)->willReturn($event);
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_error_when_user_intent_cannot_be_handled(): void
    {
        $productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $unknownUserIntent = new class implements ValueUserIntent {
            public function attributeCode(): string
            {
                return 'a_text';
            }
            public function value(): mixed
            {
                return 'new value';
            }
            public function localeCode(): ?string
            {
                return null;
            }
            public function channelCode(): ?string
            {
                return null;
            }
        }
        ;
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
                    $unknownUserIntent,
                ]);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(1);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier')->willReturn($product);
        $productUpdater->method('update');
        $this->productValidator->expects($this->never())->method('validate')->with($product);
        $this->productSaver->expects($this->never())->method('save')->with($product);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_error_when_connected_user_is_different_from_user_id(): void
    {
        $productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: []);
        $product = new Product();
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(2);
        $productUpdater->method('update');
        $this->productValidator->expects($this->never())->method('validate')->with($product);
        $this->productSaver->expects($this->never())->method('save')->with($product);
        $this->expectException(\LogicException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_updates_a_product_without_checking_user(): void
    {
        $applier = $this->createMock(UserIntentApplier::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $userIntent = new SetFamily('my-family');
        $command = UpsertProductCommand::createWithIdentifierSystemUser('identifier1', [$userIntent]);
        $product = new Product();
        $product->setCreated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->setUpdated(\DateTime::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $product->addValue(IdentifierValue::value('sku', true, 'identifier1'));
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($token);
        $user->method('getUserIdentifier')->willReturn('system');
        $token->method('getUser')->willReturn($user);
        $this->productRepository->expects($this->once())->method('findOneByIdentifier')->with('identifier1')->willReturn($product);
        $this->applierRegistry->expects($this->once())->method('getApplier')->with($userIntent)->willReturn($applier);
        $applier->expects($this->once())->method('apply')->with($userIntent, $product, -1);
        $this->productValidator->expects($this->once())->method('validate')->with($product)->willReturn(new ConstraintViolationList());
        $this->productSaver->expects($this->once())->method('save')->with($product);
        $event = new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-12 10:05:24'));
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event)->willReturn($event);
        $this->sut->__invoke($command);
    }
}
