<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class ProductValues
{
    public function __construct(private Attribute $attribute, private ChannelLocaleDataCollection $values) {}

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function getValueByChannelAndLocale(ChannelCode $channelCode, LocaleCode $localeCode)
    {
        return $this->values->getByChannelAndLocale($channelCode, $localeCode);
    }
}
