<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Processor\Denormalization;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Processor\Denormalization\ChannelProcessor;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelProcessorTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $repository;
    private SimpleFactory|MockObject $factory;
    private ObjectUpdaterInterface|MockObject $updater;
    private ValidatorInterface|MockObject $validator;
    private ObjectDetacherInterface|MockObject $objectDetacher;
    private ChannelProcessor $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->factory = $this->createMock(SimpleFactory::class);
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->objectDetacher = $this->createMock(ObjectDetacherInterface::class);
        $this->sut = new ChannelProcessor($this->repository, $this->factory, $this->updater, $this->validator, $this->objectDetacher);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ChannelProcessor::class, $this->sut);
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

        $updateCallCount = 0;
        $this->updater->expects($this->exactly(2))->method('update')
            ->with($channel, $values)
            ->willReturnCallback(function () use (&$updateCallCount): void {
                $updateCallCount++;
                if ($updateCallCount === 2) {
                    throw new InvalidPropertyException('code', 'value', 'className', 'The code could not be blank.');
                }
            });

        $this->validator->method('validate')->with($channel)->willReturn(new ConstraintViolationList());
        $this->assertSame($channel, $this->sut->process($values));

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

        // Simulate: get() returns null initially, then returns the batch after put() is called
        $batch = null;
        $executionContext->method('get')
            ->with('processed_items_batch')
            ->willReturnCallback(function () use (&$batch) {
                return $batch;
            });
        $executionContext->method('put')
            ->with('processed_items_batch', $this->anything())
            ->willReturnCallback(function (string $key, array $value) use (&$batch): void {
                $batch = $value;
            });

        $this->repository->method('findOneByIdentifier')->with('mycode')->willReturn(null);
        $this->factory->expects($this->once())->method('create')->willReturn($channel);

        $this->updater->expects($this->exactly(2))->method('update');
        $this->validator->method('validate')->with($channel)->willReturn(new ConstraintViolationList());

        $firstChannelValues = $this->getValues();
        $this->assertSame($channel, $this->sut->process($firstChannelValues));

        $secondChannelValues = $this->getValues();
        $secondChannelValues['label'] = 'Another label';
        $this->assertSame($channel, $this->sut->process($secondChannelValues));
    }

    public function test_it_remove_relationship_between_locale_and_channel_on_validation_error_for_new_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $locale = $this->createMock(LocaleInterface::class);

        $this->factory->expects($this->exactly(1))->method('create')->willReturn($channel);
        $channel->expects($this->once())->method('getId')->willReturn(null);
        $channel->expects($this->once())->method('getLocales')->willReturn([$locale]);
        $channel->expects($this->once())->method('removeLocale')->with($this->anything());
        $this->repository->method('getIdentifierProperties')->willReturn(['code']);
        $this->repository->method('findOneByIdentifier')->with($this->anything())->willReturn(null);
        $values = $this->getValues();
        $this->updater->expects($this->once())->method('update')->with($channel, $values);
        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $this->validator->expects($this->once())->method('validate')->with($channel)->willReturn($violations);
        $this->objectDetacher->expects($this->once())->method('detach')->with($channel);
        $this->expectException(InvalidItemFromViolationsException::class);
        $this->sut->process($values);
    }

    private function getValues(): array
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
