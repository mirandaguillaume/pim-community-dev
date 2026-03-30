<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ResetUniqueValidationSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueAxesCombinationSet;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use PHPUnit\Framework\TestCase;

class ResetUniqueValidationSubscriberTest extends TestCase
{
    private ResetUniqueValidationSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ResetUniqueValidationSubscriber();
    }

}
