<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\Manager;

use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use PHPUnit\Framework\TestCase;

class VersionContextTest extends TestCase
{
    private VersionContext $sut;

    protected function setUp(): void
    {
        $this->sut = new VersionContext();
    }

    public function test_it_adds_and_returns_a_default_context(): void
    {
        $this->sut->addContextInfo('my super context');
        $this->assertSame('my super context', $this->sut->getContextInfo());
    }

    public function test_it_adds_and_returns_a_context_with_fqcn(): void
    {
        $this->sut->addContextInfo('my super context with fqcn', 'MyClass');
        $this->assertSame('my super context with fqcn', $this->sut->getContextInfo('MyClass'));
    }
}
