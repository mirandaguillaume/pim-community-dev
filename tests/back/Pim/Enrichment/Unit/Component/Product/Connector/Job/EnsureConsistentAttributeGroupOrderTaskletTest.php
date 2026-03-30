<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\EnsureConsistentAttributeGroupOrderTasklet;
use Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EnsureConsistentAttributeGroupOrderTaskletTest extends TestCase
{
    private EnsureConsistentAttributeGroupOrderTasklet $sut;

    protected function setUp(): void
    {
        $this->sut = new EnsureConsistentAttributeGroupOrderTasklet();
    }

}
