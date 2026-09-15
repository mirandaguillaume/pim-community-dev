<?php

declare(strict_types=1);

namespace AkeneoTest\Channel\Integration\Currency\Controller;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Channel\Integration\ControllerIntegrationTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards Settings > Currencies: the "Change status" row action (pim_enrich_currency_toggle), the
 * PRE_SAVE listener that forbids disabling a currency used by a channel (CurrencyDisablingSubscriber,
 * registered only through #[AsEventListener]), and the currency-grid datagrid with its filters.
 * Backend guard for the deleted Behat scenario browse_currencies.feature:12.
 */
final class CurrencyControllersIntegration extends ControllerIntegrationTestCase
{
    public function test_toggling_an_inactive_currency_activates_it_and_toggling_it_again_deactivates_it(): void
    {
        self::assertFalse($this->isActivated('GBP'));
        $this->logIn('admin');

        $response = $this->toggle('GBP');

        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertSame(['successful' => true, 'message' => 'flash.currency.updated'], $this->decodeJson($response));
        self::assertTrue($this->isActivated('GBP'));

        $response = $this->toggle('GBP');

        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertSame(['successful' => true, 'message' => 'flash.currency.updated'], $this->decodeJson($response));
        self::assertFalse($this->isActivated('GBP'));
    }

    public function test_it_refuses_to_disable_a_currency_used_by_a_channel(): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        self::assertNotNull($channel);
        $linkedCurrencies = [];
        foreach ($channel->getCurrencies() as $currency) {
            $linkedCurrencies[] = $currency->getCode();
        }
        self::assertNotEmpty($linkedCurrencies);
        $linkedCurrencyCode = $linkedCurrencies[0];
        self::assertTrue($this->isActivated($linkedCurrencyCode));
        $this->logIn('admin');

        $response = $this->toggle($linkedCurrencyCode);

        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertSame(
            ['successful' => false, 'message' => 'flash.currency.error.linked_to_channel'],
            $this->decodeJson($response)
        );
        self::assertTrue(
            $this->isActivated($linkedCurrencyCode),
            'CurrencyDisablingSubscriber must stop the save of a currency used by a channel.'
        );
    }

    public function test_it_is_forbidden_for_a_user_without_the_toggle_permission(): void
    {
        $this->createUserWithRoles('julia', ['ROLE_USER']);
        $this->revokePermissionFromRole('ROLE_USER', 'pim_enrich_currency_toggle');
        $this->logIn('julia');

        $response = $this->toggle('GBP');

        $this->assertStatusCode($response, Response::HTTP_FORBIDDEN);
        self::assertFalse($this->isActivated('GBP'));
    }

    public function test_the_currency_grid_lists_every_currency_sorted_by_code_with_its_toggle_link(): void
    {
        $this->logIn('admin');

        $response = $this->callXhrRoute('pim_datagrid_load', ['alias' => 'currency-grid']);

        $this->assertStatusCode($response, Response::HTTP_OK);
        $content = $this->decodeJson($response);
        $grid = json_decode((string) $content['data'], true, 512, JSON_THROW_ON_ERROR);

        $allCodes = array_map(
            static fn (CurrencyInterface $currency): string => $currency->getCode(),
            $this->get('pim_catalog.repository.currency')->findAll()
        );
        sort($allCodes);
        self::assertSame(count($allCodes), (int) $grid['options']['totalRecords']);
        self::assertNotEmpty($grid['data']);
        self::assertSame(array_slice($allCodes, 0, count($grid['data'])), $this->rowCodes($grid['data']));
        foreach ($grid['data'] as $row) {
            self::assertSame(
                $this->router->generate('pim_enrich_currency_toggle', ['id' => $row['id']]),
                $row['toggle_link']
            );
        }
    }

    public function test_the_currency_grid_filters_on_the_activated_flag_and_searches_on_the_code(): void
    {
        $this->logIn('admin');
        self::assertSame(
            ['successful' => true, 'message' => 'flash.currency.updated'],
            $this->decodeJson($this->toggle('GBP'))
        );

        $activatedCurrencies = $this->fetchCurrencyGrid([
            '_filter' => ['activated' => ['value' => BooleanFilterType::TYPE_YES]],
        ]);
        $this->clearDoctrineUoW();
        $expectedCodes = $this->get('pim_catalog.repository.currency')->getActivatedCurrencyCodes();
        self::assertContains('GBP', $expectedCodes);
        self::assertEqualsCanonicalizing($expectedCodes, $this->rowCodes($activatedCurrencies['data']));
        self::assertSame(count($expectedCodes), (int) $activatedCurrencies['options']['totalRecords']);

        $searchedCurrencies = $this->fetchCurrencyGrid(['_filter' => ['code' => ['value' => 'GBP']]]);
        self::assertSame(['GBP'], $this->rowCodes($searchedCurrencies['data']));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function toggle(string $currencyCode): Response
    {
        $this->clearDoctrineUoW();
        $currency = $this->get('pim_catalog.repository.currency')->findOneByIdentifier($currencyCode);
        self::assertNotNull($currency);

        return $this->callXhrRoute('pim_enrich_currency_toggle', ['id' => $currency->getId()], 'POST');
    }

    private function isActivated(string $currencyCode): bool
    {
        $this->clearDoctrineUoW();

        return $this->get('pim_catalog.repository.currency')->findOneByIdentifier($currencyCode)->isActivated();
    }

    /**
     * Same request as the grid's own refresh when a filter changes: the grid data URL with the
     * state under the grid name.
     */
    private function fetchCurrencyGrid(array $gridState): array
    {
        $response = $this->callXhrRoute('oro_datagrid_index', [
            'gridName' => 'currency-grid',
            'currency-grid' => $gridState,
        ]);
        $this->assertStatusCode($response, Response::HTTP_OK);

        return $this->decodeJson($response);
    }

    /**
     * The code column is rendered by currency_label.html.twig as "CODE (label)".
     *
     * @return string[]
     */
    private function rowCodes(array $rows): array
    {
        return array_map(static function (array $row): string {
            self::assertMatchesRegularExpression('/^\s*[A-Z]{3}\b/', (string) $row['code']);
            preg_match('/^\s*([A-Z]{3})\b/', (string) $row['code'], $matches);

            return $matches[1];
        }, $rows);
    }
}
