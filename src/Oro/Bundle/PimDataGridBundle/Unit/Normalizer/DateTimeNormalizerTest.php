<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\PimDataGridBundle\Normalizer\DateTimeNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerTest extends TestCase
{
    private const TEST_TIMEZONE = 'Europe/Paris';

    private NormalizerInterface|MockObject $standardNormalizer;
    private PresenterInterface|MockObject $presenter;
    private UserContext|MockObject $userContext;
    private DateTimeNormalizer $sut;

    protected function setUp(): void
    {
        $this->standardNormalizer = $this->createMock(NormalizerInterface::class);
        $this->presenter = $this->createMock(PresenterInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new DateTimeNormalizer($this->standardNormalizer, $this->presenter, $this->userContext);
        $this->sut->userTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DateTimeNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_normalization_on_datetimes_only(): void
    {
        $datetime = new \DateTime('NOW');
        $this->assertSame(true, $this->sut->supportsNormalization($datetime, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($datetime, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_datetimes_with_paris_timezone(): void
    {
        $datetime = new \DateTime('2015-01-01 23:50:00');
        $timezone = new \DateTimeZone('Europe/Paris');
        $datetime->setTimezone($timezone);
        $this->standardNormalizer->method('normalize')->with($datetime, 'standard', [])->willReturn('2015-01-01T23:50:00+01:00');
        $this->userContext->method('getUiLocaleCode')->willReturn('en_US');
        $this->userContext->method('getUserTimezone')->willReturn('Pacific/Kiritimati');
        $this->presenter->method('present')->with(
            '2015-01-01T23:50:00+01:00',
            [
                        'locale'   => 'en_US',
                        'timezone' => 'Pacific/Kiritimati',
                    ]
        )->willReturn('01/01/2015');
        $this->assertSame('01/01/2015', $this->sut->normalize($datetime, 'datagrid'));
    }

    public function test_it_normalizes_datetimes_with_new_york_timezone(): void
    {
        $datetime = new \DateTime('2015-01-01');
        $timezone = new \DateTimeZone('America/New_York');
        $datetime->setTimezone($timezone);
        $this->standardNormalizer->method('normalize')->with($datetime, 'standard', [])->willReturn('2014-12-31T18:00:00-05:00');
        $this->userContext->method('getUiLocaleCode')->willReturn('en_US');
        $this->userContext->method('getUserTimezone')->willReturn('Pacific/Kiritimati');
        $this->presenter->method('present')->with(
            '2014-12-31T18:00:00-05:00',
            [
                        'locale'   => 'en_US',
                        'timezone' => 'Pacific/Kiritimati',
                    ]
        )->willReturn('12/31/2014');
        $this->assertSame('12/31/2014', $this->sut->normalize($datetime, 'datagrid'));
    }
}
