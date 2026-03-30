<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Mapping;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping\DelegatingClassMetadataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

class DelegatingClassMetadataFactoryTest extends TestCase
{
    private DelegatingClassMetadataFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new DelegatingClassMetadataFactory();
    }

}
