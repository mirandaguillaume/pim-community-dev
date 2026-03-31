<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\BatchQueue\Normalizer;

use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Normalizer\JobExecutionMessageNormalizer;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JobExecutionMessageNormalizerTest extends TestCase
{
    private JobExecutionMessageFactory|MockObject $jobExecutionMessageFactory;
    private JobExecutionMessageNormalizer $sut;

    protected function setUp(): void
    {
        $this->jobExecutionMessageFactory = $this->createMock(JobExecutionMessageFactory::class);
        $this->sut = new JobExecutionMessageNormalizer($this->jobExecutionMessageFactory);
    }

    public function test_it_is_a_normalizer_and_a_denormalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
        $this->assertInstanceOf(DenormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_job_messenger_normalization_only(): void
    {
        $jobMessenger = UiJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->assertSame(true, $this->sut->supportsNormalization($jobMessenger, ''));
        $jobMessenger = ImportJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->assertSame(true, $this->sut->supportsNormalization($jobMessenger, ''));
        $jobMessenger = ExportJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->assertSame(true, $this->sut->supportsNormalization($jobMessenger, ''));
        $jobMessenger = DataMaintenanceJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->assertSame(true, $this->sut->supportsNormalization($jobMessenger, ''));
        $this->assertSame(false, $this->sut->supportsNormalization(new \StdClass(), ''));
    }

    public function test_it_normalizes_a_simple_job_messenger(): void
    {
        $jobMessenger = UiJobExecutionMessage::createJobExecutionMessage(
            1,
            ['option1' => 'value1']
        );
        $normalized = $this->sut->normalize($jobMessenger);
        $this->assertIsArray($normalized);
        $this->assertIsString($normalized['id']);
        $this->assertSame(1, $normalized['job_execution_id']);
        $this->assertNotNull($normalized['created_time']);
        $this->assertNull($normalized['updated_time']);
        $this->assertSame(['option1' => 'value1'], $normalized['options']);
    }

    public function test_it_normalizes_a_full_job_message(): void
    {
        $jobMessenger = ImportJobExecutionMessage::createJobExecutionMessageFromNormalized([
                    'id' => '215ee791-1c40-4c60-82fb-cb017d6bcb90',
                    'job_execution_id' => 2,
                    'created_time' => '2020-01-01',
                    'updated_time' => '2020-02-01',
                    'options' => ['option1' => 'value1'],
                ]);
        $normalized = $this->sut->normalize($jobMessenger);
        $this->assertIsArray($normalized);
        $this->assertSame('215ee791-1c40-4c60-82fb-cb017d6bcb90', $normalized['id']);
        $this->assertSame(2, $normalized['job_execution_id']);
        $this->assertSame((new \DateTime('2020-01-01'))->format('c'), $normalized['created_time']);
        $this->assertSame((new \DateTime('2020-02-01'))->format('c'), $normalized['updated_time']);
        $this->assertSame(['option1' => 'value1'], $normalized['options']);
    }

    public function test_it_supports_job_messenger_denormalization_only(): void
    {
        $this->assertSame(true, $this->sut->supportsDenormalization([], UiJobExecutionMessage::class));
        $this->assertSame(true, $this->sut->supportsDenormalization([], ImportJobExecutionMessage::class));
        $this->assertSame(true, $this->sut->supportsDenormalization([], ExportJobExecutionMessage::class));
        $this->assertSame(true, $this->sut->supportsDenormalization([], DataMaintenanceJobExecutionMessage::class));
        $this->assertSame(false, $this->sut->supportsDenormalization([], 'Unknown'));
    }

    public function test_it_denormalizes_a_job_execution_message(): void
    {
        $message = UiJobExecutionMessage::createJobExecutionMessage(1, []);
        $normalized = ['test'];
        $this->jobExecutionMessageFactory->method('buildFromNormalized')->with($normalized, UiJobExecutionMessage::class)->willReturn($message);
        $this->assertSame($message, $this->sut->denormalize($normalized, UiJobExecutionMessage::class));
    }
}
