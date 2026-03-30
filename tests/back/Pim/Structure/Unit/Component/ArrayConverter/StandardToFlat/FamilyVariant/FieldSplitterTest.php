<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant\FieldSplitter;
use PHPUnit\Framework\TestCase;

class FieldSplitterTest extends TestCase
{
    private FieldSplitter $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldSplitter();
    }

}
