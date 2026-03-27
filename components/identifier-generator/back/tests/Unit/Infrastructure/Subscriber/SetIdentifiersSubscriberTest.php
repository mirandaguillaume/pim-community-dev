<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFreeTextHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchEmptyIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\MatchIdentifierGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber\SetIdentifiersSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetIdentifiersSubscriberTest extends TestCase
{
    private IdentifierGeneratorRepository|MockObject $identifierGeneratorRepository;
    private ValidatorInterface|MockObject $validator;
    private MetadataFactoryInterface|MockObject $metadataFactory;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private LoggerInterface|MockObject $logger;
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private SetIdentifiersSubscriber $sut;

    protected function setUp(): void
    {
        $this->identifierGeneratorRepository = $this->createMock(IdentifierGeneratorRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->sut = new SetIdentifiersSubscriber(
            $this->identifierGeneratorRepository,
            new GenerateIdentifierHandler(new \ArrayIterator([
                new GenerateFreeTextHandler(),
            ])),
            $this->validator,
            $this->metadataFactory,
            $this->eventDispatcher,
            new MatchIdentifierGeneratorHandler(new \ArrayIterator([
                new MatchEmptyIdentifierHandler(),
            ])),
            $this->logger,
            $this->attributeRepository
        );
    }

    public function test_it_should_be_a_set_identifiers_subscriber(): void
    {
        $this->assertInstanceOf(SetIdentifiersSubscriber::class, $this->sut);
    }

    public function test_it_should_generate_an_identifier(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $valueMetadata = $this->createMock(ClassMetadataInterface::class);
        $valuePropertyMetadata = $this->createMock(PropertyMetadataInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $this->identifierGeneratorRepository->expects($this->once())->method('getAll')->willReturn([$this->getIdentifierGenerator()]);
        $attribute->expects($this->atLeastOnce())->method('isMainIdentifier')->willReturn(true);
        $this->attributeRepository->expects($this->atLeastOnce())->method('findOneByIdentifier')->with('sku')->willReturn($attribute);
        $product->expects($this->once())->method('isEnabled')->willReturn(true);
        $product->expects($this->once())->method('getFamily')->willReturn(null);
        $product->expects($this->once())->method('getCategoryCodes')->willReturn([]);
        $product->expects($this->once())->method('getValues')->willReturn(new WriteValueCollection([]));
        $product->expects($this->atLeastOnce())->method('addValue');

        $constraint = new Length(null, 10);

        $this->validator->expects($this->exactly(2))->method('validate')->willReturnCallback(
            function () {
                return new ConstraintViolationList([]);
            }
        );

        $this->metadataFactory->expects($this->atLeastOnce())->method('getMetadataFor')->willReturn($valueMetadata);
        $valueMetadata->expects($this->once())->method('getPropertyMetadata')->with('data')->willReturn([$valuePropertyMetadata]);
        $valuePropertyMetadata->expects($this->once())->method('getConstraints')->willReturn([$constraint]);

        $this->logger->expects($this->once())->method('notice')->with(
            '[akeneo.pim.identifier_generator] Successfully generated an identifier for the sku attribute',
            ['identifier_attribute_code' => 'sku']
        );
        $this->eventDispatcher->expects($this->never())->method('dispatch');
        $this->sut->setIdentifier(new GenericEvent($product));
    }

    public function test_it_should_rollback_when_product_identifier_is_invalid(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $valueMetadata = $this->createMock(ClassMetadataInterface::class);
        $valuePropertyMetadata = $this->createMock(PropertyMetadataInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->atLeastOnce())->method('isMainIdentifier')->willReturn(true);
        $this->attributeRepository->expects($this->atLeastOnce())->method('findOneByIdentifier')->with('sku')->willReturn($attribute);
        $this->identifierGeneratorRepository->expects($this->once())->method('getAll')->willReturn([$this->getIdentifierGenerator()]);
        $product->expects($this->atLeastOnce())->method('addValue');
        $product->expects($this->once())->method('isEnabled')->willReturn(true);
        $product->expects($this->once())->method('getFamily')->willReturn(null);
        $product->expects($this->once())->method('getCategoryCodes')->willReturn([]);
        $product->expects($this->once())->method('getValues')->willReturn(new WriteValueCollection([]));

        $this->validator->expects($this->exactly(2))->method('validate')->willReturnCallback(
            function () use ($product) {
                $args = \func_get_args();
                if ($args[0] === $product && $args[1] === null && ($args[2] ?? null) === ['identifiers']) {
                    return new ConstraintViolationList([
                        new ConstraintViolation('', '', [], '', '', ''),
                    ]);
                }

                return new ConstraintViolationList([]);
            }
        );

        $this->metadataFactory->expects($this->atLeastOnce())->method('getMetadataFor')->willReturn($valueMetadata);
        $valueMetadata->expects($this->once())->method('getPropertyMetadata')->with('data')->willReturn([$valuePropertyMetadata]);
        $valuePropertyMetadata->expects($this->once())->method('getConstraints')->willReturn([]);

        $product->expects($this->atLeastOnce())->method('removeValue');
        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->logger->expects($this->never())->method('notice');
        $this->sut->setIdentifier(new GenericEvent($product));
    }

    public function test_it_should_rollback_when_product_value_is_invalid(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $valueMetadata = $this->createMock(ClassMetadataInterface::class);
        $valuePropertyMetadata = $this->createMock(PropertyMetadataInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->atLeastOnce())->method('isMainIdentifier')->willReturn(true);
        $this->attributeRepository->expects($this->atLeastOnce())->method('findOneByIdentifier')->with('sku')->willReturn($attribute);
        $this->identifierGeneratorRepository->expects($this->once())->method('getAll')->willReturn([$this->getIdentifierGenerator()]);
        $product->expects($this->atLeastOnce())->method('addValue');
        $product->expects($this->once())->method('isEnabled')->willReturn(true);
        $product->expects($this->once())->method('getFamily')->willReturn(null);
        $product->expects($this->once())->method('getCategoryCodes')->willReturn([]);
        $product->expects($this->once())->method('getValues')->willReturn(new WriteValueCollection([]));

        $this->validator->expects($this->exactly(2))->method('validate')->willReturnCallback(
            function () use ($product) {
                $args = \func_get_args();
                if ($args[0] === $product && $args[1] === null && ($args[2] ?? null) === ['identifiers']) {
                    return new ConstraintViolationList([]);
                }

                return new ConstraintViolationList([
                    new ConstraintViolation('', '', [], '', '', ''),
                ]);
            }
        );

        $this->metadataFactory->expects($this->atLeastOnce())->method('getMetadataFor')->willReturn($valueMetadata);
        $valueMetadata->expects($this->once())->method('getPropertyMetadata')->with('data')->willReturn([$valuePropertyMetadata]);
        $valuePropertyMetadata->expects($this->once())->method('getConstraints')->willReturn([]);

        $product->expects($this->atLeastOnce())->method('removeValue');
        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->logger->expects($this->never())->method('notice');
        $this->sut->setIdentifier(new GenericEvent($product));
    }

    public function test_it_should_do_nothing_if_subject_is_not_a_product(): void
    {
        $this->identifierGeneratorRepository->expects($this->never())->method('getAll');
        $this->sut->setIdentifier(new GenericEvent(new \stdClass()));
    }

    private function getIdentifierGenerator(): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('AKN')]),
            LabelCollection::fromNormalized(['fr' => 'Mon générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
    }
}
