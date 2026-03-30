<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Comment\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard\CommentNormalizer;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CommentNormalizerTest extends TestCase
{
    private CommentNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new CommentNormalizer();
    }

}
