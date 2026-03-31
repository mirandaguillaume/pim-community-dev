<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Form\Type;

use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Form\Type\DatagridViewType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatagridViewTypeTest extends TestCase
{
    private DatagridViewType $sut;

    protected function setUp(): void
    {
        $this->sut = new DatagridViewType(DatagridView::class);
    }

    public function test_it_is_a_form_type(): void
    {
        $this->assertInstanceOf(AbstractType::class, $this->sut);
    }

    public function test_it_has_a_block_prefix(): void
    {
        $this->assertSame('pim_datagrid_view', $this->sut->getBlockPrefix());
    }

    public function test_it_has_view_and_edit_permission_fields(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $addedFields = [];
        $builder->expects($this->exactly(3))->method('add')->willReturnCallback(
            function (string $name, string $type, array $options = []) use ($builder, &$addedFields) {
                $addedFields[] = [$name, $type];
                return $builder;
            }
        );
        $this->sut->buildForm($builder, []);

        $this->assertSame('label', $addedFields[0][0]);
        $this->assertSame(TextType::class, $addedFields[0][1]);
        $this->assertSame('order', $addedFields[1][0]);
        $this->assertSame(HiddenType::class, $addedFields[1][1]);
        $this->assertSame('filters', $addedFields[2][0]);
        $this->assertSame(HiddenType::class, $addedFields[2][1]);
    }

    public function test_it_does_not_map_the_fields_to_the_entity_by_default(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        $resolver->method('setDefaults')->with([
                        'data_class' => DatagridView::class,
                    ])->willReturn($resolver);
        $this->sut->configureOptions($resolver);
        $resolver->method('setDefaults')->with([
                        'data_class' => DatagridView::class,
                    ]);
    }
}
