<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Doctrine\ORM;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\ReferenceDataUniqueCodeChecker;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\FieldMapping;
use PHPUnit\Framework\TestCase;

class ReferenceDataUniqueCodeCheckerTest extends TestCase
{
    private ReferenceDataUniqueCodeChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataUniqueCodeChecker();
    }

}
