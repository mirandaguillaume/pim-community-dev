<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as DoctrineEntityType;

class EntityFilterTypeTest extends AbstractTypeTestCase
{
    private \Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();

        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $this->type = new EntityFilterType($translator);

        $types = [
            new FilterType($translator),
            new ChoiceFilterType($translator),
            new DoctrineEntityType($registry),
            $this->type,
        ];

        $this->formExtensions[] = new CustomFormExtension($types);

        parent::setUp();
    }

    /**
     * @return EntityFilterType
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testGetName()
    {
        $this->assertEquals(EntityFilterType::NAME, $this->type->getBlockPrefix());
    }

    public function testGetParent()
    {
        $this->assertEquals(ChoiceFilterType::class, $this->type->getParent());
    }

    /**
     * {@inheritDoc}
     */
    public static function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'    => DoctrineEntityType::class,
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
    ) {
        // bind method should be tested in functional test
        $this->expectNotToPerformAssertions();
    }

    /**
     * {@inheritDoc}
     */
    public static function bindDataProvider()
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
