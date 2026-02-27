<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Dictionary;

/**
 * @license   https://opensource.org/licenses/MIT MIT
 * @source    https://github.com/mekras/php-speller
 */
class SpellerDictionary
{
    public function __construct(private readonly string $dictionaryPath) {}

    public function getPath(): string
    {
        return $this->dictionaryPath;
    }
}
