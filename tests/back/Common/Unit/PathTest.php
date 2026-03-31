<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Common;

use Akeneo\Test\Common\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    private Path $sut;

    protected function setUp(): void
    {
        $this->sut = new Path();
    }

}
