<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductQuerySorterPassTest extends TestCase
{
    private RegisterProductQuerySorterPass $sut;

    protected function setUp(): void
    {
        $this->sut = new RegisterProductQuerySorterPass();
    }

}
