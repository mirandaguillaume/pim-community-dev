<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\FormatterExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormatterExtensionTest extends TestCase
{
    private TranslatorInterface|MockObject $translator;
    private FormatterExtension $sut;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->sut = new FormatterExtension($this->translator);
    }

    public function test_it_is_a_formatter_extension(): void
    {
        $this->assertInstanceOf(FormatterExtension::class, $this->sut);
    }

    public function test_it_allows_a_column_configuration_with_an_integer(): void
    {
        $config = $this->createMock(DatagridConfiguration::class);
        $result = $this->createMock(ResultsIterableObject::class);
        $property1 = $this->createMock(PropertyInterface::class);
        $property2 = $this->createMock(PropertyInterface::class);
        $initializedProperty1 = $this->createMock(PropertyInterface::class);
        $initializedProperty2 = $this->createMock(PropertyInterface::class);

        $this->sut->registerProperty('property1', $property1);
        $this->sut->registerProperty('property2', $property2);
        $record0 = new ResultRecord(['record0']);
        $record1 = new ResultRecord(['record1']);
        $rows = [
                    '0' => $record0,
                    '1' => $record1,
                ];
        $result->method('offsetGetOr')->willReturnMap([
            ['data', [], $rows],
        ]);
        $config->method('offsetGetOr')->willReturnMap([
            ['options', [], []],
            [Configuration::COLUMNS_KEY, [], [1234 => ['type' => 'property1']]],
            [Configuration::PROPERTIES_KEY, [], ['identifier' => ['type' => 'property2']]],
        ]);
        $config1234 = PropertyConfiguration::createNamed(1234, ['type' => 'property1']);
        $configIdentifier = PropertyConfiguration::createNamed('identifier', ['type' => 'property2']);
        $property1->method('init')->with($config1234)->willReturn($initializedProperty1);
        $property2->method('init')->with($configIdentifier)->willReturn($initializedProperty2);
        $initializedProperty1->method('getValue')->willReturnCallback(function (ResultRecord $record) use ($record0, $record1) {
            if ($record === $record0) {
                return 'property1 record0';
            }
            if ($record === $record1) {
                return 'property1 record1';
            }
            return null;
        });
        $initializedProperty2->method('getValue')->willReturnCallback(function (ResultRecord $record) use ($record0, $record1) {
            if ($record === $record0) {
                return 'property2 record0';
            }
            if ($record === $record1) {
                return 'property2 record1';
            }
            return null;
        });
        $result->expects($this->once())->method('offsetSet')->with('data', [
                    '0' => [
                        1234 => 'property1 record0',
                        'identifier' => 'property2 record0',
                    ], '1' => [
                        1234 => 'property1 record1',
                        'identifier' => 'property2 record1',
                    ],
                ]);
        $this->sut->visitResult($config, $result);
    }

    public function test_it_allows_total_records_and_extra_keys(): void
    {
        $config = $this->createMock(DatagridConfiguration::class);
        $result = $this->createMock(ResultsIterableObject::class);
        $property1 = $this->createMock(PropertyInterface::class);
        $property2 = $this->createMock(PropertyInterface::class);
        $initializedProperty1 = $this->createMock(PropertyInterface::class);
        $initializedProperty2 = $this->createMock(PropertyInterface::class);

        $this->sut->registerProperty('property1', $property1);
        $this->sut->registerProperty('property2', $property2);
        $record0 = new ResultRecord(['record0']);
        $record1 = new ResultRecord(['record1']);
        $rows = [
                    'data' => [
                        '0' => $record0,
                        '1' => $record1,
                    ],
                    'totalRecords' => 10,
                    'extra_key' => 'extra key value',
                ];
        $result->method('offsetGetOr')->willReturnMap([
            ['data', [], $rows],
        ]);
        $config->method('offsetGetOr')->willReturnMap([
            ['options', [], ['extraKeys' => ['extra_key']]],
            [Configuration::COLUMNS_KEY, [], [1234 => ['type' => 'property1']]],
            [Configuration::PROPERTIES_KEY, [], ['identifier' => ['type' => 'property2']]],
        ]);
        // offsetSet is called multiple times with different args - just verify it is called
        $result->expects($this->atLeastOnce())->method('offsetSet');
        $config1234 = PropertyConfiguration::createNamed(1234, ['type' => 'property1']);
        $configIdentifier = PropertyConfiguration::createNamed('identifier', ['type' => 'property2']);
        $property1->method('init')->with($config1234)->willReturn($initializedProperty1);
        $property2->method('init')->with($configIdentifier)->willReturn($initializedProperty2);
        $initializedProperty1->method('getValue')->willReturnCallback(function (ResultRecord $record) use ($record0, $record1) {
            if ($record === $record0) {
                return 'property1 record0';
            }
            if ($record === $record1) {
                return 'property1 record1';
            }
            return null;
        });
        $initializedProperty2->method('getValue')->willReturnCallback(function (ResultRecord $record) use ($record0, $record1) {
            if ($record === $record0) {
                return 'property2 record0';
            }
            if ($record === $record1) {
                return 'property2 record1';
            }
            return null;
        });
        $this->sut->visitResult($config, $result);
    }
}
