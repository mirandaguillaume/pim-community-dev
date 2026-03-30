<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Job\ComputeFamilyVariantStructureChangesTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeFamilyVariantStructureChangesTaskletTest extends TestCase
{
    private ComputeFamilyVariantStructureChangesTasklet $sut;

    protected function setUp(): void
    {
        $this->sut = new ComputeFamilyVariantStructureChangesTasklet();
    }

    private function cursorWillYield(Collaborator $cursor, array $yield): void
    {
            $cursor->rewind()->shouldBeCalledOnce();
            $valid = \array_fill(0, \count($yield), true);
            $valid[] = false;
            $cursor->valid()->shouldBeCalledTimes(count($valid))->willReturn(...$valid);
            $cursor->next()->shouldBeCalledTimes(count($yield));
            $cursor->current()->shouldBeCalledTimes(count($yield))->willReturn(...$yield);
        }
}
