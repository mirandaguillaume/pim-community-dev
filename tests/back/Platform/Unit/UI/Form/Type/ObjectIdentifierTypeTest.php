<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Tests\Unit\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntityToIdentifierTransformer;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectIdentifierTypeTest extends TestCase
{
    private ObjectRepository|MockObject $repository;
    private ObjectIdentifierType $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ObjectRepository::class);
        $this->sut = new ObjectIdentifierType();
    }

    public function test_its_parent_is_the_hidden_type(): void
    {
        $this->assertSame(HiddenType::class, $this->sut->getParent());
    }

    public function test_its_block_prefix_is_pim_object_identifier(): void
    {
        $this->assertSame('pim_object_identifier', $this->sut->getBlockPrefix());
    }

    public function test_it_requires_a_repository_option(): void
    {
        $resolver = new OptionsResolver();
        $this->sut->configureOptions($resolver);

        $this->expectException(MissingOptionsException::class);

        $resolver->resolve([]);
    }

    public function test_it_rejects_a_repository_that_is_not_an_object_repository(): void
    {
        $resolver = new OptionsResolver();
        $this->sut->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);

        $resolver->resolve(['repository' => new \stdClass()]);
    }

    public function test_it_defaults_multiple_to_true_and_the_identifier_to_id(): void
    {
        $resolver = new OptionsResolver();
        $this->sut->configureOptions($resolver);

        $options = $resolver->resolve(['repository' => $this->repository]);

        $this->assertTrue($options['multiple']);
        $this->assertSame(',', $options['delimiter']);
        $this->assertSame('id', $options['identifier']);
    }

    public function test_it_rejects_a_non_boolean_multiple_option(): void
    {
        $resolver = new OptionsResolver();
        $this->sut->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);

        $resolver->resolve(['repository' => $this->repository, 'multiple' => 'yes']);
    }

    public function test_it_adds_an_entity_to_identifier_view_transformer(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->with($this->isInstanceOf(EntityToIdentifierTransformer::class), true);

        $this->sut->buildForm($builder, [
            'repository' => $this->repository,
            'multiple' => true,
            'delimiter' => ',',
            'identifier' => 'id',
        ]);
    }
}
