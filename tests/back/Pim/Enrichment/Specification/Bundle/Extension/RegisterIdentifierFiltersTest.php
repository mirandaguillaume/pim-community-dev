<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Extension;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Extension\RegisterIdentifierFilters;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class RegisterIdentifierFiltersTest extends TestCase
{
    private GetAttributes|MockObject $getAttributes;
    private GetAttributeTranslations|MockObject $getAttributeTranslations;
    private UserContext|MockObject $userContext;
    private RequestParameters|MockObject $requestParams;
    private RequestStack|MockObject $requestStack;
    private UserInterface|MockObject $user;
    private LocaleInterface|MockObject $locale;
    private BuildBefore|MockObject $buildBefore;
    private DatagridConfiguration|MockObject $datagridConfiguration;
    private RegisterIdentifierFilters $sut;

    protected function setUp(): void
    {
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->getAttributeTranslations = $this->createMock(GetAttributeTranslations::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->requestParams = $this->createMock(RequestParameters::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->locale = $this->createMock(LocaleInterface::class);
        $this->buildBefore = $this->createMock(BuildBefore::class);
        $this->datagridConfiguration = $this->createMock(DatagridConfiguration::class);
        $this->sut = new RegisterIdentifierFilters($this->getAttributes, $this->getAttributeTranslations, $this->userContext, $this->requestParams, $this->requestStack);
        $this->locale->method('getCode')->willReturn('fr_FR');
        $this->user->method('getCatalogLocale')->willReturn($this->locale);
        $this->userContext->method('getUser')->willReturn($this->user);
        $this->buildBefore->method('getConfig')->willReturn($this->datagridConfiguration);
    }

    public function test_it_adds_attribute_identifier_as_filters(): void
    {
        $this->getAttributeTranslations->method('byAttributeCodesAndLocale')->with(['sku'], 'fr_FR')->willReturn([
                    'sku' => 'Sku',
                ]);
        $this->getAttributes->method('forType')->with('pim_catalog_identifier')->willReturn([
                    'sku' => new Attribute(
                        'sku',
                        AttributeTypes::IDENTIFIER,
                        [],
                        false,
                        false,
                        null,
                        null,
                        false,
                        AttributeTypes::BACKEND_TYPE_TEXT,
                        [],
                    ),
                ]);
        $familyFilter = [
                    'type' => 'product_family',
                    'label' => 'pim_datagrid.filters.family.label',
                    'data_name' => 'family',
                    'options' => [
                        'field_options' => [
                            'multiple' => true,
                            'attr' => [
                                'empty_choice' => true,
                            ],
                        ],
                    ],
                ];
        $this->datagridConfiguration->method('offsetGet')->with('filters')->willReturn(['columns' => ['family' => $familyFilter]]);
        $this->datagridConfiguration->expects($this->once())->method('offsetAddToArray')->with('filters', [
                    'columns' => [
                        'family' => $familyFilter,
                        'sku' => [
                            'type' => 'product_value_string',
                            'ftype' => 'identifier',
                            'label' => 'Sku',
                            'data_name' => 'sku',
                            'options' => [
                                'field_options' => [
                                    'attr' => [
                                        'choice_list' => true,
                                        'empty_choice' => true,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]);
        $this->sut->buildBefore($this->buildBefore);
    }

    public function test_it_falls_back_to_attribute_code_if_label_is_not_found(): void
    {
        $this->user->method('getCatalogLocale')->willReturn(null);
        $this->getAttributeTranslations->method('byAttributeCodesAndLocale')->with(['sku'], 'en_US')->willReturn([]);
        $this->getAttributes->method('forType')->with('pim_catalog_identifier')->willReturn([
                    'sku' => new Attribute(
                        'sku',
                        AttributeTypes::IDENTIFIER,
                        [],
                        false,
                        false,
                        null,
                        null,
                        false,
                        AttributeTypes::BACKEND_TYPE_TEXT,
                        [],
                    ),
                ]);
        $familyFilter = [
                    'type' => 'product_family',
                    'label' => 'pim_datagrid.filters.family.label',
                    'data_name' => 'family',
                    'options' => [
                        'field_options' => [
                            'multiple' => true,
                            'attr' => [
                                'empty_choice' => true,
                            ],
                        ],
                    ],
                ];
        $this->datagridConfiguration->method('offsetGet')->with('filters')->willReturn(['columns' => ['family' => $familyFilter]]);
        $this->datagridConfiguration->expects($this->once())->method('offsetAddToArray')->with('filters', [
                    'columns' => [
                        'family' => $familyFilter,
                        'sku' => [
                            'type' => 'product_value_string',
                            'ftype' => 'identifier',
                            'label' => '[sku]',
                            'data_name' => 'sku',
                            'options' => [
                                'field_options' => [
                                    'attr' => [
                                        'choice_list' => true,
                                        'empty_choice' => true,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]);
        $this->sut->buildBefore($this->buildBefore);
    }
}
