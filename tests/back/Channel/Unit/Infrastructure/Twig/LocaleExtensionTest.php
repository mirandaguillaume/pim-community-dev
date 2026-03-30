<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Twig;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Twig\LocaleExtension;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\TestCase;
use Twig\Node\Node;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LocaleExtensionTest extends TestCase
{
    private LocaleExtension $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleExtension();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}
