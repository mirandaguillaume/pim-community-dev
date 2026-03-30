<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeNormalizer;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerTest extends TestCase
{
    private AttributeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeNormalizer();
    }

}
