<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\DataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PHPUnit\Framework\TestCase;

class ResultRecordTest extends TestCase
{
    public function test_it_stores_array_data_as_value_container(): void
    {
        $record = new ResultRecord(['name' => 'foo', 'value' => 42]);
        $this->assertSame('foo', $record->getValue('name'));
        $this->assertSame(42, $record->getValue('value'));
    }

    public function test_it_stores_object_as_value_container(): void
    {
        $obj = new \stdClass();
        $obj->name = 'bar';
        $record = new ResultRecord($obj);
        $this->assertSame('bar', $record->getValue('name'));
    }

    public function test_it_separates_numeric_keyed_objects_from_string_keyed_values(): void
    {
        $obj = new class {
            public function getName(): string
            {
                return 'from_object';
            }
        };
        $record = new ResultRecord([0 => $obj, 'extra' => 'extra_value']);

        $this->assertSame('from_object', $record->getValue('name'));
        $this->assertSame('extra_value', $record->getValue('extra'));
    }

    public function test_it_puts_array_data_into_container_only_when_non_empty(): void
    {
        $obj = new class {
            public function getId(): int
            {
                return 99;
            }
        };
        // All entries are numeric-keyed objects => no array data pushed
        $record = new ResultRecord([0 => $obj]);
        $this->assertSame(99, $record->getValue('id'));
    }

    public function test_it_throws_when_value_not_found(): void
    {
        $record = new ResultRecord(['name' => 'foo']);
        $this->expectException(\Exception::class);
        $record->getValue('nonexistent');
    }

    public function test_it_returns_root_entity_when_first_container_is_object(): void
    {
        $obj = new \stdClass();
        $record = new ResultRecord($obj);
        $this->assertSame($obj, $record->getRootEntity());
    }

    public function test_it_returns_null_root_entity_when_first_container_is_array(): void
    {
        $record = new ResultRecord(['name' => 'foo']);
        $this->assertNull($record->getRootEntity());
    }

    public function test_it_accesses_object_via_getter_methods(): void
    {
        $obj = new class {
            public function getName(): string
            {
                return 'getter_value';
            }

            public function isActive(): bool
            {
                return true;
            }
        };
        $record = new ResultRecord($obj);
        $this->assertSame('getter_value', $record->getValue('name'));
        $this->assertTrue($record->getValue('active'));
    }

    public function test_it_reads_option_value_when_object_has_no_property(): void
    {
        $obj = new \stdClass();
        $record = new ResultRecord($obj, ['fallback' => 'option_value']);
        $this->assertSame('option_value', $record->getValue('fallback'));
    }

    public function test_it_handles_non_array_non_object_data_gracefully(): void
    {
        $record = new ResultRecord('string_data');
        $this->expectException(\LogicException::class);
        $record->getValue('anything');
    }
}
