<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CommonAttributeCollectionTest extends TestCase
{
    private CommonAttributeCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new CommonAttributeCollection();
    }

}
