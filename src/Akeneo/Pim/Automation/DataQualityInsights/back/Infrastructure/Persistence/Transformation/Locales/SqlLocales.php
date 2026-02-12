<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlLocales implements LocalesInterface
{
    private array $localeIdsByCodes;

    private array $localeCodesByIds;

    private bool $localesLoaded;

    public function __construct(private readonly Connection $dbConnection)
    {
        $this->localeIdsByCodes = [];
        $this->localeCodesByIds = [];
        $this->localesLoaded = false;
    }

    public function getIdByCode(string $code): ?int
    {
        if (false === $this->localesLoaded) {
            $this->loadLocales();
        }

        return $this->localeIdsByCodes[$code] ?? null;
    }

    public function getCodeById(int $id): ?string
    {
        if (false === $this->localesLoaded) {
            $this->loadLocales();
        }

        return $this->localeCodesByIds[$id] ?? null;
    }

    private function loadLocales(): void
    {
        $locales = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(id, code) FROM pim_catalog_locale WHERE is_activated = 1;'
        )->fetchOne();

        if ($locales) {
            $this->localeCodesByIds = json_decode((string) $locales, true, 512, JSON_THROW_ON_ERROR);
            $this->localeIdsByCodes = array_flip($this->localeCodesByIds);
        }

        $this->localesLoaded = true;
    }
}
