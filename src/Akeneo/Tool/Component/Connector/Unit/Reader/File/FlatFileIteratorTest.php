<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Connector\Reader\File\FlatFileIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FlatFileIteratorTest extends TestCase
{
    private FlatFileIterator $sut;

    protected function setUp(): void
    {
        $this->sut = new FlatFileIterator('csv', $this->getPath() . DIRECTORY_SEPARATOR . 'with_media.csv', [
            'reader_options' => [
                'fieldDelimiter' => ';',
            ],
        ]);
    }

    public function test_it_throws_exception_with_invalid_filename(): void
    {
        $this->expectException(FileNotFoundException::class);
        new FlatFileIterator('csv', $this->getPath() . DIRECTORY_SEPARATOR . 'unknown_file.csv', [
                    'reader_options' => [
                        'fieldDelimiter' => ';',
                    ],
                ]);
    }

    public function test_it_gets_current_row(): void
    {
        $this->sut->rewind();
        $this->sut->next();
        $this->assertSame([
                        'SKU-001',
                        'door',
                        'sku-001.jpg',
                        'sku-001.txt',
                    ], $this->sut->current());
    }

    public function test_it_gets_current_row_from_xlsx(): void
    {
        $this->sut = new FlatFileIterator('xlsx', $this->getPath() . DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx');
        $this->sut->rewind();
        $this->sut->next();
        $this->assertSame([
                        'SKU-001',
                        'boots',
                        'CROSS',
                        'winter_boots',
                        'Donec',
                        "dictum magna.\n\nLorem ispum\nEst",
                    ], $this->sut->current());
    }

    public function test_it_gets_current_row_from_an_archive(): void
    {
        $this->sut = new FlatFileIterator('csv', $this->getPath() . DIRECTORY_SEPARATOR . 'caterpillar_import.zip', [
                    'reader_options' => [
                        'fieldDelimiter' => ';',
                    ],
                ]);
        $this->sut->rewind();
        $this->sut->next();
        $this->assertSame([
                        'CAT-001',
                        'boots',
                        'winter_collection',
                        'Caterpillar 1',
                        'Model 1 boots',
                        'cat_001.png',
                        'black',
                        '37',
                    ], $this->sut->current());
    }

    public function test_it_returns_null_at_the_end_of_file(): void
    {
        $this->sut->rewind();
        $this->sut->next();
        $this->sut->next();
        $this->assertNull($this->sut->current());
    }

    public function test_it_returns_directory_from_filepath(): void
    {
        $this->sut->rewind();
        $this->assertSame($this->getPath(), $this->sut->getDirectoryPath());
    }

    public function test_it_returns_directory_created_for_archive(): void
    {
        $this->sut = new FlatFileIterator('csv', $this->getPath() . DIRECTORY_SEPARATOR . 'caterpillar_import.zip');
        $this->sut->rewind();
        $this->assertSame($this->getPath() . DIRECTORY_SEPARATOR . 'caterpillar_import', $this->sut->getDirectoryPath());
    }

    public function test_it_returns_key(): void
    {
        $this->sut->rewind();
        $this->sut->next();
        $this->assertSame(2, $this->sut->key());
    }

    public function test_it_returns_true_if_current_position_is_valid(): void
    {
        $this->sut->rewind();
        $this->sut->next();
        $this->assertSame(true, $this->sut->valid());
    }

    public function test_it_returns_false_if_current_position_is_not_valid(): void
    {
        $this->sut->rewind();
        $this->sut->next();
        $this->sut->next();
        $this->assertSame(false, $this->sut->valid());
    }

    private function getPath()
    {
        return __DIR__
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . '..'
               . DIRECTORY_SEPARATOR . 'tests'
               . DIRECTORY_SEPARATOR . 'legacy'
               . DIRECTORY_SEPARATOR . 'features'
               . DIRECTORY_SEPARATOR . 'Context'
               . DIRECTORY_SEPARATOR . 'fixtures';
    }
}
