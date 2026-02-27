<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class RequiredAttributesMaskForChannelAndLocale
{
    /**
     * This separator should not be allowed in attribute codes
     *
     * @var string
     */
    final public const ATTRIBUTE_CHANNEL_LOCALE_SEPARATOR = '-';

    /**
     * @param string[] $mask
     */
    public function __construct(
        private readonly string $channelCode,
        private readonly string $localeCode,
        /**
         * ['name-ecommerce-en_US', 'sku-<all_channel>-<all_locales>', ...]
         */
        private readonly array $mask
    ) {
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function mask(): array
    {
        return $this->mask;
    }
}
