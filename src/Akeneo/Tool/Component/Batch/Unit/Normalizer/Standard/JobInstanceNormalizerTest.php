<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Normalizer\Standard;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Normalizer\Standard\JobInstanceNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JobInstanceNormalizerTest extends TestCase
{
    private JobInstanceNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new JobInstanceNormalizer();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JobInstanceNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_job_instance_normalization_into_json_and_xml(): void
    {
        $jobinstance = $this->createMock(JobInstance::class);

        $this->assertSame(false, $this->sut->supportsNormalization($jobinstance, 'csv'));
        $this->assertSame(false, $this->sut->supportsNormalization($jobinstance, 'json'));
        $this->assertSame(false, $this->sut->supportsNormalization($jobinstance, 'xml'));
        $this->assertSame(true, $this->sut->supportsNormalization($jobinstance, 'standard'));
    }

    public function test_it_normalizes_job_instance(): void
    {
        $jobinstance = $this->createMock(JobInstance::class);

        $jobinstance->method('getCode')->willReturn('product_export');
        $jobinstance->method('getLabel')->willReturn('Product export');
        $jobinstance->method('getConnector')->willReturn('myconnector');
        $jobinstance->method('getType')->willReturn('EXPORT');
        $jobinstance->method('getJobName')->willReturn('csv_product_export');
        $jobinstance->method('getRawParameters')->willReturn([
                        'delimiter' => ';',
                    ]);
        $jobinstance->method('isScheduled')->willReturn(false);
        $jobinstance->method('getAutomation')->willReturn(null);
        $this->assertSame([
                        'code'          => 'product_export',
                        'job_name'      => 'csv_product_export',
                        'label'         => 'Product export',
                        'connector'     => 'myconnector',
                        'type'          => 'EXPORT',
                        'configuration' => ['delimiter' => ';'],
                        'automation'    => null,
                        'scheduled'     => false,
                    ], $this->sut->normalize($jobinstance));
    }
}
