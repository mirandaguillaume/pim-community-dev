<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader\File\Yaml;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Exception\InvalidYamlFileException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Validator\ConstraintViolationList;

class ReaderTest extends TestCase
{
    private ArrayConverterInterface|MockObject $converter;
    private Reader $sut;

    protected function setUp(): void
    {
        $this->converter = $this->createMock(ArrayConverterInterface::class);
        $this->sut = new Reader($this->converter, 'products');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader::class, $this->sut);
    }

    public function test_it_is_an_item_reader_step_execution_and_uploaded_file_aware(): void
    {
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Batch\Item\ItemReaderInterface::class, $this->sut);
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface::class, $this->sut);
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface::class, $this->sut);
    }

    public function test_it_return_empty_count_on_invalid_file(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'rules');
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_incorrectly_formatted_yml_file.yml');
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $incorrectlyFormattedFilePath]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $this->assertSame(0, $this->sut->totalItems());
    }

    public function test_it_return_an_error_if_file_does_not_contain_the_root_level(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'an_non_existent_root_level');
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml');
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $incorrectlyFormattedFilePath]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $this->sut->shouldThrow(InvalidYamlFileException::class)->during('read');
    }

    public function test_it_return_an_error_if_file_does_not_exist(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products');
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => 'a_non_existent_file.yml']);
        $this->sut->shouldThrow(FileNotFoundException::class)->during('read');
    }

    public function test_it_return_item_count(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml');
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $incorrectlyFormattedFilePath]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $this->assertSame(3, $this->sut->totalItems());
    }

    public function test_it_initializes_the_summary_info_if_the_yaml_file_is_not_correctly_formatted(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'rules', false, false);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_incorrectly_formatted_yml_file.yml');
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $incorrectlyFormattedFilePath]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $this->assertNull($this->sut->read());
    }

    public function test_it_reads_entities_from_a_yml_file_one_by_one_incrementing_summary_info_for_each_one(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products', false, false);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml')]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $stepExecution->expects($this->exactly(3))->method('incrementSummaryInfo')->with('item_position');
        $this->converter->method('convert')->with(['sku' => 'mug_akeneo'])->willReturn(['sku' => 'mug_akeneo']);
        $this->converter->method('convert')->with([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ])->willReturn([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ]);
        $this->converter->method('convert')->with(['sku' => 'mouse_akeneo'])->willReturn(['sku' => 'mouse_akeneo']);
        $this->assertSame([
                    'sku' => 'mug_akeneo',
                ], $this->sut->read());
        $this->assertSame([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ], $this->sut->read());
        $this->assertSame([
                    'sku' => 'mouse_akeneo',
                ], $this->sut->read());
    }

    public function test_it_skips_an_item_in_case_of_conversion_error(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products', false, false);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml')]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $stepExecution->expects($this->once())->method('getSummaryInfo')->with('item_position');
        $data = [
                    'sku'  => 'mug_akeneo',
                ];
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with("skip");
        $this->converter->method('convert')->with($data, $this->anything())->willThrowException(new DataArrayConversionException('message', 0, null, new ConstraintViolationList()));
        $this->sut->shouldThrow(InvalidItemFromViolationsException::class)->during('read');
    }

    public function test_it_reads_entities_from_a_yml_file_one_by_one(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products', false, false);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml')]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with($this->anything());
        $this->converter->method('convert')->with(['sku' => 'mug_akeneo'])->willReturn(['sku' => 'mug_akeneo']);
        $this->converter->method('convert')->with([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ])->willReturn([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ]);
        $this->converter->method('convert')->with(['sku' => 'mouse_akeneo'])->willReturn(['sku' => 'mouse_akeneo']);
        $this->assertSame([
                    'sku' => 'mug_akeneo',
                ], $this->sut->read());
        $this->assertSame([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ], $this->sut->read());
        $this->assertSame([
                    'sku' => 'mouse_akeneo',
                ], $this->sut->read());
    }

    public function test_it_reads_several_entities_from_a_yml_file_incrementing_summary_info(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products', true, false);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml')]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $result = [
                    'mug_akeneo' => [
                        'sku' => 'mug_akeneo',
                    ],
                    't_shirt_akeneo_purple' => [
                        'sku'   => 't_shirt_akeneo_purple',
                        'color' => 'purple',
                    ],
                    'mouse_akeneo' => [
                        'sku' => 'mouse_akeneo',
                    ],
                ];
        $this->converter->method('convert')->with($result)->willReturn($result);
        $this->sut->setStepExecution($stepExecution);
        $this->assertSame($result, $this->sut->read());
    }

    public function test_it_reads_several_entities_without_code_from_a_yml_file(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products', true, 'sku');
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => realpath(__DIR__ . '/../../../fixtures/fake_products_without_code.yml')]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $result = [
                    'mug_akeneo_blue' => [
                        'color' => 'blue',
                        'sku'   => 'mug_akeneo_blue',
                    ],
                    't_shirt_akeneo_s_purple' => [
                        'color' => 'purple',
                        'size'  => 'S',
                        'sku'   => 't_shirt_akeneo_s_purple',
                    ],
                    'mug_akeneo_purple' => [
                        'color' => 'purple',
                        'sku'   => 'mug_akeneo_purple',
                    ],
                ];
        $this->converter->method('convert')->with($result)->willReturn($result);
        $this->assertSame($result, $this->sut->read());
    }

    public function test_it_initializes_the_class(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut = new Reader($this->converter, 'products', false, false);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml')]);
        $stepExecution->expects($this->exactly(1))->method('setSummary')->with(['item_position' => 0]);
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $this->converter->method('convert')->with(['sku' => 'mug_akeneo'])->willReturn(['sku' => 'mug_akeneo']);
        $this->converter->method('convert')->with([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ])->willReturn([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ]);
        $this->converter->method('convert')->with(['sku' => 'mouse_akeneo'])->willReturn(['sku' => 'mouse_akeneo']);
        $this->assertSame([
                    'sku' => 'mug_akeneo',
                ], $this->sut->read());
        $this->sut->initialize();
        $this->assertSame([
                    'sku' => 'mug_akeneo',
                ], $this->sut->read());
        $this->assertSame([
                    'sku'   => 't_shirt_akeneo_purple',
                    'color' => 'purple',
                ], $this->sut->read());
        $this->assertSame([
                    'sku' => 'mouse_akeneo',
                ], $this->sut->read());
    }
}
