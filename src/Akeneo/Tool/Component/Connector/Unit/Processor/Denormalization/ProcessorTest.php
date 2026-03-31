<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Processor\Denormalization;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\Processor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * We test with a channel but it could be anything
 */
class ProcessorTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $repository;
    private SimpleFactory|MockObject $factory;
    private ObjectUpdaterInterface|MockObject $updater;
    private ValidatorInterface|MockObject $validator;
    private ObjectDetacherInterface|MockObject $objectDetacher;
    private Processor $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->factory = $this->createMock(SimpleFactory::class);
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->objectDetacher = $this->createMock(ObjectDetacherInterface::class);
        $this->sut = new Processor($this->repository, $this->factory, $this->updater, $this->validator, $this->objectDetacher);
    }

    public function test_it_is_a_processor(): void
    {
        $this->assertInstanceOf(ItemProcessorInterface::class, $this->sut);
        $this->assertInstanceOf(StepExecutionAwareInterface::class, $this->sut);
    }

    public function test_it_updates_an_existing_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->repository->method('getIdentifierProperties')->willReturn(['code']);
        $this->repository->method('findOneByIdentifier')->with('mycode')->willReturn($channel);
        $channel->method('getId')->willReturn(42);
        $values = $this->getValues();
        $this->updater->expects($this->once())->method('update')->with($channel, $values);
        $this->validator->method('validate')->with($channel)->willReturn(new ConstraintViolationList());
        $this->assertSame($channel, $this->sut->process($values));
    }

    public function test_it_skips_a_channel_when_update_fails(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->repository->method('getIdentifierProperties')->willReturn(['code']);
        $this->repository->method('findOneByIdentifier')->with($this->anything())->willReturn($channel);
        $channel->method('getId')->willReturn(42);
        $values = $this->getValues();
        $this->updater->method('update')->with($channel, $values)->willThrowException(new InvalidPropertyException('code', 'value', 'className', 'The code could not be blank.'));
        $this->expectException(InvalidItemException::class);
        $this->sut->process($values);
    }

    public function test_it_skips_a_channel_when_object_is_invalid(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->repository->method('getIdentifierProperties')->willReturn(['code']);
        $this->repository->method('findOneByIdentifier')->with($this->anything())->willReturn($channel);
        $channel->method('getId')->willReturn(42);
        $values = $this->getValues();
        $this->updater->expects($this->once())->method('update')->with($channel, $values);
        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $this->validator->method('validate')->with($channel)->willReturn($violations);
        $this->objectDetacher->expects($this->once())->method('detach')->with($channel);
        $this->expectException(InvalidItemException::class);
        $this->sut->process($values);
    }

    public function test_it_does_not_create_the_same_channel_twice_in_the_same_batch(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $executionContext = $this->createMock(ExecutionContext::class);
        $channel = $this->createMock(ChannelInterface::class);

        $this->sut->setStepExecution($stepExecution);
        $this->repository->method('getIdentifierProperties')->willReturn(['code']);
        $stepExecution->method('getExecutionContext')->willReturn($executionContext);
        $processedItems = null;
        $executionContext->method('get')->with('processed_items_batch')->willReturnCallback(
            function () use (&$processedItems) {
                return $processedItems;
            }
        );
        $executionContext->method('put')->willReturnCallback(
            function (string $key, $value) use (&$processedItems) {
                $processedItems = $value;
            }
        );
        $this->repository->method('findOneByIdentifier')->with('mycode')->willReturn(null);
        // Factory creates only once (channel is found in execution context on 2nd call)
        $this->factory->expects($this->exactly(1))->method('create')->willReturn($channel);
        $this->updater->expects($this->exactly(2))->method('update');
        $this->validator->method('validate')->with($channel)->willReturn(new ConstraintViolationList());
        $firstChannelValues = $this->getValues();
        $this->assertSame($channel, $this->sut->process($firstChannelValues));
        $secondChannelValues = $this->getValues();
        $secondChannelValues['label'] = 'Another label';
        $this->assertSame($channel, $this->sut->process($secondChannelValues));
    }

    protected function getValues()
    {
        return [
            'code'       => 'mycode',
            'label'      => 'Ecommerce',
            'locales'    => ['en_US', 'fr_FR'],
            'currencies' => ['EUR', 'USD'],
            'tree'       => 'master_catalog',
            'color'      => 'orange',
        ];
    }
}
