<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Writer\Database;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Writer\Database\AttributeGroupWriter;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\TestCase;

class AttributeGroupWriterTest extends TestCase
{
    private AttributeGroupWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroupWriter();
    }

}
