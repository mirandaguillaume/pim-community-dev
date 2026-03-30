<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\DateTimeWithUserTimezoneProperty;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateTimeWithUserTimezonePropertyTest extends TestCase
{
    private TranslatorInterface|MockObject $translator;
    private PresenterInterface|MockObject $presenter;
    private UserContext|MockObject $userContext;
    private DateTimeWithUserTimezoneProperty $sut;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->presenter = $this->createMock(PresenterInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new DateTimeWithUserTimezoneProperty($this->translator, $this->presenter, $this->userContext);
        $this->sut->init(PropertyConfiguration::create([
        'name'          => 'a_date',
        'label'         => 'A date',
        'type'          => 'datetime_with_user_timezone',
        'frontend_type' => 'datetime',
        ]));
    }

    public function test_it_formats_a_datetime_with_user_timezone(): void
    {
        $datetime = new \DateTime('2018-03-20T18:13');
        $this->userContext->method('getUiLocaleCode')->willReturn('en_GB');
        $this->userContext->method('getUserTimezone')->willReturn('Pacific/Kiritimati');
        $this->presenter->expects($this->once())->method('present')->with(
            $datetime,
            [
                        'locale'   => 'en_GB',
                        'timezone' => 'Pacific/Kiritimati',
                    ]
        );
        $this->sut->getValue(new ResultRecord(['a_date' => $datetime]));
    }
}
