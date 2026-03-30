<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\MediaExporterPathGenerator;
use PHPUnit\Framework\TestCase;

class MediaExporterPathGeneratorTest extends TestCase
{
    private MediaExporterPathGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new MediaExporterPathGenerator();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MediaExporterPathGenerator::class, $this->sut);
    }

    public function test_it_generates_the_path(): void
    {
        $value = [
                    'locale' => null,
                    'scope'  => null,
                ];
        $options = ['identifier' => 'sku001', 'code' => 'picture'];
        $this->assertSame('files/sku001/picture/', $this->sut->generate($value, $options));
    }

    public function test_it_generates_the_path_when_the_value_is_localisable(): void
    {
        $value = [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                ];
        $options = ['identifier' => 'sku001', 'code' => 'picture'];
        $this->assertSame('files/sku001/picture/fr_FR/', $this->sut->generate($value, $options));
    }

    public function test_it_generates_the_path_when_the_value_is_scopable(): void
    {
        $value = [
                    'locale' => null,
                    'scope'  => 'ecommerce',
                ];
        $options = ['identifier' => 'sku001', 'code' => 'picture'];
        $this->assertSame('files/sku001/picture/ecommerce/', $this->sut->generate($value, $options));
    }

    public function test_it_generates_the_path_when_the_value_is_localisable_and_scopable(): void
    {
        $value = [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                ];
        $options = ['identifier' => 'sku001', 'code' => 'picture'];
        $this->assertSame('files/sku001/picture/fr_FR/ecommerce/', $this->sut->generate($value, $options));
    }

    public function test_it_generates_the_path_when_the_sku_contains_slash(): void
    {
        $value = [
                    'locale' => null,
                    'scope'  => null,
                ];
        $options = ['identifier' => 'sku/001', 'code' => 'picture'];
        $this->assertSame('files/sku_001/picture/', $this->sut->generate($value, $options));
    }
}
