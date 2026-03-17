<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Prophecy\Argument;

class AttributeRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->name = 'attribute';
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_attribute_repository()
    {
        $this->shouldImplement(AttributeRepositoryInterface::class);
    }

    function it_finds_the_axis_attribute(
        $em,
        QueryBuilder $queryBuilder,
        Expr $exprObj,
        Expr\Func $inExpr,
        Expr\Comparison $notScopable,
        Expr\Comparison $notLocalizable,
        Query $query
    ) {
        $queryBuilder->expr()->willReturn($exprObj);
        $exprObj->in('a.type', [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT])
            ->willReturn($inExpr);
        $inExpr->__toString()->willReturn('a.type IN (...)');
        $exprObj->neq('a.scopable', 1)->willReturn($notScopable);
        $notScopable->__toString()->willReturn('a.scopable <> 1');
        $exprObj->neq('a.localizable', 1)->willReturn($notLocalizable);
        $notLocalizable->__toString()->willReturn('a.localizable <> 1');


        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('a')->willReturn($queryBuilder);
        $queryBuilder->select('a.id')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', a.code, \']\')) as label')->willReturn($queryBuilder);
        $queryBuilder->addSelect('a.code')->willReturn($queryBuilder);
        $queryBuilder->from('attribute', 'a', null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('a.translations', 't')->willReturn($queryBuilder);
        $queryBuilder->andWhere($inExpr)->willReturn($queryBuilder);
        $queryBuilder->andWhere($notScopable)->willReturn($queryBuilder);
        $queryBuilder->andWhere($notLocalizable)->willReturn($queryBuilder);
        $queryBuilder->andWhere('t.locale = :locale')->willReturn($queryBuilder);
        $queryBuilder->setParameter('locale', 'en_US')->willReturn($queryBuilder);
        $queryBuilder->orderBy('t.label')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['id' => 11, 'label' => 'a'],
            ['id' => 12, 'label' => 'b'],
            ['id' => 10, 'label' => 's'],
        ]);

        $this->findAvailableAxes('en_US')->shouldReturn([
            ['id' => 11, 'label' => 'a'],
            ['id' => 12, 'label' => 'b'],
            ['id' => 10, 'label' => 's'],
        ]);
    }
}
