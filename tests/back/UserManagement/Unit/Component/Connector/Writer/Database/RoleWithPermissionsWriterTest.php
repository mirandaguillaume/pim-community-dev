<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Connector\Writer\Database\RoleWithPermissionsWriter;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PHPUnit\Framework\TestCase;

class RoleWithPermissionsWriterTest extends TestCase
{
    private RoleWithPermissionsWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissionsWriter();
    }

}
