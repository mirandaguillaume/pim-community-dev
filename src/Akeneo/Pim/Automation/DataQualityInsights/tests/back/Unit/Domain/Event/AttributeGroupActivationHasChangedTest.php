<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Event;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use PHPUnit\Framework\TestCase;

class AttributeGroupActivationHasChangedTest extends TestCase
{
    private AttributeGroupActivationHasChanged $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_normalized(): void
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $this->sut = new AttributeGroupActivationHasChanged('the_code', false, $date);
        $this->assertSame([
                    'attribute_group_code' => 'the_code',
                    'new_is_activated' => false,
                    'updated_at' => '2020-11-24T22:02:12+00:00',
                ], $this->sut->normalize());
    }

    public function test_it_can_be_denormalized(): void
    {
        $this->sut = new AttributeGroupActivationHasChanged('the_code', false, new \DateTimeImmutable());
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
                    'attribute_group_code' => 'the_code',
                    'new_is_activated' => true,
                    'updated_at' => '2020-11-24T22:02:12+00:00',
                ];
        $this->assertEquals(new AttributeGroupActivationHasChanged('the_code', true, $date), $this->sut->denormalize($normalized));
    }

    public function test_it_cannot_be_denormalized_with_wrong_code(): void
    {
        $this->sut = new AttributeGroupActivationHasChanged('the_code', false, new \DateTimeImmutable());
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
                    'attribute_group_code' => 12,
                    'new_is_activated' => true,
                    'updated_at' => '2020-11-24T22:02:12+00:00',
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->denormalize($normalized);
    }

    public function test_it_cannot_be_denormalized_with_wrong_value(): void
    {
        $this->sut = new AttributeGroupActivationHasChanged('the_code', false, new \DateTimeImmutable());
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
                    'attribute_group_code' => 'the_code',
                    'new_is_activated' => 'true',
                    'updated_at' => '2020-11-24T22:02:12+00:00',
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->denormalize($normalized);
    }

    public function test_it_cannot_be_denormalized_with_wrong_date(): void
    {
        $this->sut = new AttributeGroupActivationHasChanged('the_code', false, new \DateTimeImmutable());
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
                    'attribute_group_code' => 'the_code',
                    'new_is_activated' => true,
                    'updated_at' => 'wrong date',
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->denormalize($normalized);
    }
}
