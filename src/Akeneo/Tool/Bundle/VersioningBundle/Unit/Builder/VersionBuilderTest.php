<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Component\Versioning\Model\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionBuilderTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private VersionFactory|MockObject $versionFactory;
    private VersionBuilder $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->versionFactory = $this->createMock(VersionFactory::class);
        $this->sut = new VersionBuilder($this->normalizer, $this->versionFactory);
    }

    public function test_it_builds_versions_for_versionable_entities(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $version = $this->createMock(Version::class);

        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $product->method('getUuid')->willReturn($uuid);
        $this->normalizer->method('normalize')->with($product, 'flat', [])->willReturn(['bar' => 'baz']);
        $this->versionFactory->method('create')->with(/* TODO: convert Argument matcher */ Argument::Any(), null, $uuid, 'foo', null)->willReturn($version);
        $version->method('setVersion')->with(1)->willReturn($version);
        $version->method('setSnapshot')->with(['bar' => 'baz'])->willReturn($version);
        $version->method('setChangeset')->with(['bar' => ['old' => '', 'new' => 'baz']])->willReturn($version);
        $this->sut->buildVersion($product, 'foo');
    }

    public function test_it_creates_pending_version(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $pending = $this->createMock(Version::class);

        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $product->method('getUuid')->willReturn($uuid);
        $this->versionFactory->method('create')->with(/* TODO: convert Argument matcher */ Argument::Any(), null, $uuid, 'baz', null)->willReturn($pending);
        $pending->method('getChangeset')->willReturn($pending);
        $pending->method('setChangeset')->with([])->willReturn($pending);
        $pending->method('getAuthor')->willReturn('baz');
        $pending->method('isPending')->willReturn(true);
        $version = $this->createPendingVersion($product, 'baz', []);
        $version->shouldBeAnInstanceOf(Version::class);
        $version->getAuthor()->shouldReturn('baz');
        $version->isPending()->shouldReturn(true);
    }

    public function test_it_builds_pending_versions(): void
    {
        $pending = $this->createMock(Version::class);

        $pending->method('setVersion')->with(1)->willReturn($pending);
        $pending->method('setSnapshot')->with(['foo' => 'bar'])->willReturn($pending);
        $pending->method('getChangeset')->willReturn(['foo' => 'bar']);
        $pending->expects($this->once())->method('setChangeset')->with(['foo' => ['old' => '', 'new' => 'bar']])->willReturn($pending);
        $this->sut->buildPendingVersion($pending);
    }

    public function test_it_builds_pending_versions_with_attribute_with_numeric_code(): void
    {
        $pending = $this->createMock(Version::class);

        $pending->method('setVersion')->with(1)->willReturn($pending);
        $pending->method('setSnapshot')->with([12_345_678 => 'bar'])->willReturn($pending);
        $pending->method('getChangeset')->willReturn([12_345_678 => 'bar']);
        $pending->expects($this->once())->method('setChangeset')->with([12_345_678 => ['old' => '', 'new' => 'bar']])->willReturn($pending);
        $this->sut->buildPendingVersion($pending);
    }

    public function test_it_compares_versions(): void
    {
        $pending = $this->createMock(Version::class);
        $previousVersion = $this->createMock(Version::class);

        $previousVersion->method('getVersion')->willReturn(1);
        $previousVersion->method('getSnapshot')->willReturn(['test' => 'old_data', 'description' => "old description"]);
        $pending->method('setVersion')->with(2)->willReturn($pending);
        $pending->method('setSnapshot')->with(['test' => 'old_data', "name" => "pending name", "description" => "old description"])->willReturn($pending);
        $pending->method('getChangeset')->willReturn(['test' => 'pending_data', "name" => "pending name"]);
        $pending->method('setChangeset')->with(["name" => ["old" => "", "new" => "pending name"]])->willReturn($pending);
        $this->sut->buildPendingVersion($pending, $previousVersion);
    }

    public function test_it_builds_versions_and_handle_correctly_the_old_versioning_date_format(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);
        $previousVersion = $this->createMock(Version::class);
        $version = $this->createMock(Version::class);

        $this->normalizer->method('normalize')->with($productModel, 'flat', [])->willReturn([
                    'name' => 'bar',
                    'date_with_new_format' => '2020-01-01T00:00:00+00:00',
                    'date_with_old_format' => '2020-01-01T00:00:00+00:00',
                    'date_with_old_format_and_timezone' => '2020-01-01T12:00:00+12:00',
                    'date_with_old_format_has_changed' => '2020-01-02T00:00:00+00:00',
                ]);
        $this->versionFactory->method('create')->with($this->anything(), 100, null, 'julia', null)->willReturn($version);
        $productModel->method('getId')->willReturn(100);
        $previousVersion->method('getVersion')->willReturn(1);
        $previousVersion->method('getSnapshot')->willReturn([
                    'name' => 'foo',
                    'date_with_new_format' => '2020-01-01T00:00:00+00:00',
                    'date_with_old_format' => '2020-01-01',
                    'date_with_old_format_and_timezone' => '2020-01-01',
                    'date_with_old_format_has_changed' => '2020-01-01',
                ]);
        $version->method('setVersion')->with(2)->willReturn($version);
        $version->method('setSnapshot')->with([
                    'name' => 'bar',
                    'date_with_new_format' => '2020-01-01T00:00:00+00:00',
                    'date_with_old_format' => '2020-01-01T00:00:00+00:00',
                    'date_with_old_format_and_timezone' => '2020-01-01T12:00:00+12:00',
                    'date_with_old_format_has_changed' => '2020-01-02T00:00:00+00:00',
                ])->willReturn($version);
        $version->method('setChangeset')->with([
                    'name' => ['old' => 'foo', 'new' => 'bar'],
                    'date_with_old_format_has_changed' => ['old' => '2020-01-01', 'new' => '2020-01-02T00:00:00+00:00'],
                ])->willReturn($version);
        $this->sut->buildVersion($productModel, 'julia', $previousVersion, null);
    }
}
