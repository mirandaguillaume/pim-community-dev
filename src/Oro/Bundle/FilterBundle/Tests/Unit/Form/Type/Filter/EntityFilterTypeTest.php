<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EntityFilterTypeTest extends AbstractTypeTestCase
{
    private \Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();

        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $types = [
            new FilterType($translator),
            new ChoiceFilterType($translator),
            new EntityType($registry),
        ];

        $this->formExtensions[] = new CustomFormExtension($types);

        parent::setUp();

        $this->type = new EntityFilterType($translator);
    }

    /**
     * @return EntityFilterType
     */
    protected function getTestFormType(): \Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType
    {
        return $this->type;
    }

    public function testGetName(): void
    {
        $this->assertEquals(EntityFilterType::NAME, $this->type->getBlockPrefix());
    }

    public function testGetParent(): void
    {
        $this->assertEquals(ChoiceFilterType::NAME, $this->type->getParent());
    }

    /**
     * {@inheritDoc}
     */
    public static function configureOptionsDataProvider(): array
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'    => 'entity',
                    'field_options' => [],
                    'translatable'  => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider bindDataProvider
     */
    #[\Override]
    public function testBindData(
        array $bindData,
        array $formData,
        array $viewData,
        array $customOptions = []
    ): void {
        // bind method should be tested in functional test
    }

    /**
     * {@inheritDoc}
     */
    public static function bindDataProvider(): array
    {
        return [
            'empty' => [
                'bindData' => [],
                'formData' => [],
                'viewData' => [],
            ],
        ];
    }
}
