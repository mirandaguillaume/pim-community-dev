<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\GroupSavingOptionsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class GroupSavingOptionsResolverTest extends TestCase
{
    private GroupSavingOptionsResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupSavingOptionsResolver();
    }

}
