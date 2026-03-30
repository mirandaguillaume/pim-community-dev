<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Form\Type;

use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Form\Type\DatagridViewType;
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

        $builder->method('add')->with('label', TextType::class, ['required' => true])->willReturn($builder);
        $builder->method('add')->with('order', HiddenType::class)->willReturn($builder);
        $builder->expects($this->once())->method('add')->with('filters', HiddenType::class)->willReturn($builder);
        $this->sut->buildForm($builder, []);
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
