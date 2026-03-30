<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Normalizer;

use Akeneo\UserManagement\Component\Normalizer\DateTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerTest extends TestCase
{
    private DateTimeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new DateTimeNormalizer();
    }

}
