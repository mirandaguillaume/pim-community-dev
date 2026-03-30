<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Normalizer\GroupNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerTest extends TestCase
{
    private GroupNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupNormalizer();
    }

}
