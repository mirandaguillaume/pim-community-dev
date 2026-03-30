<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Job\JobParameters\EmptyConstraintCollectionProvider;
use Symfony\Component\Validator\Constraints\Collection;

class EmptyConstraintCollectionProviderTest extends TestCase
{
    private EmptyConstraintCollectionProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new EmptyConstraintCollectionProvider(['job_name']);
    }

    public function test_it_is_a_contraint_collection_provider(): void
    {
        $this->assertInstanceOf(ConstraintCollectionProviderInterface::class, $this->sut);
    }

    public function test_it_provides_default_constraint_collection(): void
    {
        $this->sut->getConstraintCollection()->shouldReturnAnInstanceOf(Collection::class);
    }
}
