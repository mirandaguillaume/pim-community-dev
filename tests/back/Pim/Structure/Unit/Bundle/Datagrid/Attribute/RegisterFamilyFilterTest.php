<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Pim\Structure\Bundle\Datagrid\Attribute\RegisterFamilyFilter;
use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAllFamiliesLabelByLocaleQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Common\IterableObject;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegisterFamilyFilterTest extends TestCase
{
    private GetAllFamiliesLabelByLocaleQueryInterface|MockObject $familiesLabelByLocaleQuery;
    private UserContext|MockObject $userContext;
    private RegisterFamilyFilter $sut;

    protected function setUp(): void
    {
        $this->familiesLabelByLocaleQuery = $this->createMock(GetAllFamiliesLabelByLocaleQueryInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new RegisterFamilyFilter($this->familiesLabelByLocaleQuery, $this->userContext);
    }

    public function test_it_is_a_register_family_filter(): void
    {
        $this->assertInstanceOf(RegisterFamilyFilter::class, $this->sut);
    }

    public function test_it_registers_the_family_filter(): void
    {
        $event = $this->createMock(BuildBefore::class);
        $config = $this->createMock(IterableObject::class);

        $this->userContext->method('getCurrentLocaleCode')->willReturn('en_US');
        $this->familiesLabelByLocaleQuery->method('execute')->with('en_US')->willReturn([
                    'family1' => 'A family 1',
                    'family2' => 'A family 2',
                ]);
        $event->method('getConfig')->willReturn($config);
        $config->expects($this->once())->method('offsetAddToArrayByPath')->with(FilterConfiguration::COLUMNS_PATH, [
                    'family' => [
                        'type' => 'datagrid_attribute_family_filter',
                        'ftype' => 'choice',
                        'label' => 'Family',
                        'data_name' => 'families',
                        'options' => [
                            'field_options' => [
                                'multiple' => true,
                                'choices' => [
                                    'A family 1' => 'family1',
                                    'A family 2' => 'family2',
                                ],
                            ],
                        ],
                    ]
                ]);
        $this->sut->buildBefore($event);
    }
}
