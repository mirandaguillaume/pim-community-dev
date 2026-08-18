<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Tests\Unit\Form\Transformer;

use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\EntityToIdentifierTransformer;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityToIdentifierTransformerTest extends TestCase
{
    private ObjectRepository|MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ObjectRepository::class);
    }

    public function test_it_transforms_null_to_null(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, false);

        $this->assertNull($sut->transform(null));
    }

    public function test_it_transforms_a_single_entity_to_its_identifier(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, false);

        $this->assertSame(42, $sut->transform($this->entity(42)));
    }

    public function test_it_rejects_a_non_object_when_not_multiple(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, false);

        $this->expectException(UnexpectedTypeException::class);

        $sut->transform('not an object');
    }

    public function test_it_transforms_multiple_entities_into_a_delimited_list_of_identifiers(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, true, null, ',');

        $this->assertSame('1,2', $sut->transform([$this->entity(1), $this->entity(2)]));
    }

    public function test_it_transforms_multiple_entities_into_an_array_of_identifiers_when_there_is_no_delimiter(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, true, null, null);

        $this->assertSame([1, 2], $sut->transform([$this->entity(1), $this->entity(2)]));
    }

    public function test_it_rejects_a_non_array_when_multiple(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, true);

        $this->expectException(UnexpectedTypeException::class);

        $sut->transform($this->entity(1));
    }

    public function test_it_reverse_transforms_null_to_null(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, false);

        $this->assertNull($sut->reverseTransform(null));
    }

    public function test_it_reverse_transforms_a_single_identifier_to_the_matching_entity(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, false);
        $entity = $this->entity(42);
        $this->repository->method('findOneBy')->with(['id' => 42])->willReturn($entity);

        $this->assertSame($entity, $sut->reverseTransform(42));
    }

    public function test_it_reverse_transforms_a_delimited_list_of_identifiers_to_the_matching_entities(): void
    {
        $sut = new EntityToIdentifierTransformer($this->repository, true, null, ',');
        $entities = [$this->entity(1), $this->entity(2)];
        $this->repository->method('findBy')->with(['id' => ['1', '2']])->willReturn($entities);

        $this->assertSame($entities, $sut->reverseTransform('1,2'));
    }

    private function entity(int $id): object
    {
        return new class ($id) {
            public function __construct(public readonly int $id) {}
        };
    }
}
