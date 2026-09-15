<?php

declare(strict_types=1);

namespace AkeneoTest\Channel\Integration\Channel\Controller\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Channel\Integration\ControllerIntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards channel creation from the Settings > Channels creation form: POST pim_enrich_channel_rest_post
 * with the body pim/saver/channel sends, the error map the form puts on its fields, the channel the
 * edit page reloads after the save, and the endpoints that feed the form's select fields.
 * Backend guard for the deleted @critical Behat scenario create_channel.feature:8.
 */
final class CreateChannelControllerIntegration extends ControllerIntegrationTestCase
{
    public function test_it_creates_the_channel_sent_by_the_creation_form(): void
    {
        self::assertNotContains('fr_FR', $this->activatedLocaleCodes());
        $this->logIn('admin');

        $response = $this->postChannel($this->creationFormPayload());

        $this->assertStatusCode($response, Response::HTTP_OK);
        $created = $this->decodeJson($response);
        self::assertSame('mobile', $created['code']);
        self::assertSame('Mobile app', $created['labels']['en_US']);
        self::assertSame('master', $created['category_tree']);
        self::assertSame(['EUR'], $created['currencies']);
        self::assertSame(['fr_FR'], array_column($created['locales'], 'code'));
        self::assertSame('pim-channel-edit-form', $created['meta']['form']);
        self::assertIsInt($created['meta']['id']);

        $channel = $this->findChannel('mobile');
        self::assertNotNull($channel);
        self::assertSame('master', $channel->getCategory()->getCode());
        self::assertSame(['EUR'], $this->currencyCodesOf($channel));
        self::assertSame(['fr_FR'], array_values($channel->getLocaleCodes()));
        self::assertContains('fr_FR', $this->activatedLocaleCodes(), 'A locale added to a new channel must be activated.');

        $response = $this->callXhrRoute('pim_enrich_channel_rest_get', ['identifier' => 'mobile']);
        $this->assertStatusCode($response, Response::HTTP_OK);
        $fetched = $this->decodeJson($response);
        self::assertSame('mobile', $fetched['code']);
        self::assertSame('Mobile app', $fetched['labels']['en_US']);
        self::assertSame($created['meta']['id'], $fetched['meta']['id']);
    }

    public function test_it_returns_the_violation_for_the_category_tree_field_when_no_tree_is_given(): void
    {
        $this->logIn('admin');
        $payload = $this->creationFormPayload();
        unset($payload['category_tree']);

        $response = $this->postChannel($payload);

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertSame(
            ['category' => ['message' => 'This value should not be blank.']],
            $this->decodeJson($response)
        );
        self::assertNull($this->findChannel('mobile'));
    }

    public function test_it_returns_the_violation_for_the_currencies_field_when_a_currency_is_not_activated(): void
    {
        self::assertNotContains('GBP', $this->activatedCurrencyCodes());
        $this->logIn('admin');

        $response = $this->postChannel(['currencies' => ['GBP']] + $this->creationFormPayload());

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertSame(
            ['currencies[0]' => ['message' => 'The currency "GBP" has to be activated.']],
            $this->decodeJson($response)
        );
        self::assertNull($this->findChannel('mobile'));
    }

    public function test_it_rejects_a_body_that_is_not_valid_json(): void
    {
        $this->logIn('admin');

        $response = $this->callXhrRoute('pim_enrich_channel_rest_post', [], 'POST', [], '{"code": "mobile"');

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertSame(['message' => 'Invalid json message received'], $this->decodeJson($response));
        self::assertNull($this->findChannel('mobile'));
    }

    public function test_it_redirects_a_non_xhr_request_without_creating_the_channel(): void
    {
        $this->logIn('admin');

        $response = $this->callRoute(
            'pim_enrich_channel_rest_post',
            [],
            'POST',
            ['CONTENT_TYPE' => 'application/json'],
            [],
            json_encode($this->creationFormPayload(), JSON_THROW_ON_ERROR)
        );

        self::assertTrue($response->isRedirect('/'), sprintf('Expected a redirection to "/", got %d.', $response->getStatusCode()));
        self::assertNull($this->findChannel('mobile'));
    }

    public function test_it_is_forbidden_for_a_user_without_the_channel_creation_permission(): void
    {
        $this->createUserWithRoles('julia', ['ROLE_USER']);
        $this->revokePermissionFromRole('ROLE_USER', 'pim_enrich_channel_create');
        $this->logIn('julia');

        $response = $this->postChannel($this->creationFormPayload());

        $this->assertStatusCode($response, Response::HTTP_FORBIDDEN);
        self::assertNull($this->findChannel('mobile'));
    }

    public function test_the_creation_form_select_sources_return_the_category_trees_and_only_the_activated_currencies(): void
    {
        $this->logIn('admin');

        $response = $this->callXhrRoute('pim_enrich_currency_rest_index');
        $this->assertStatusCode($response, Response::HTTP_OK);
        $currencies = $this->decodeJson($response);
        self::assertEqualsCanonicalizing($this->activatedCurrencyCodes(), array_keys($currencies));
        self::assertArrayHasKey('EUR', $currencies);
        self::assertArrayNotHasKey('GBP', $currencies);
        self::assertSame(['code' => 'EUR'], $currencies['EUR']);

        $response = $this->callXhrRoute('pim_enrich_channel_category_trees_get');
        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertContains('master', array_column($this->decodeJson($response), 'code'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * The body pim/saver/channel posts for the creation form: the initial model of
     * controller/channel/edit.js without "meta", locales sent as codes.
     */
    private function creationFormPayload(): array
    {
        return [
            'code' => 'mobile',
            'currencies' => ['EUR'],
            'locales' => ['fr_FR'],
            'category_tree' => 'master',
            'conversion_units' => [],
            'labels' => ['en_US' => 'Mobile app'],
        ];
    }

    private function postChannel(array $payload): Response
    {
        return $this->callXhrRoute(
            'pim_enrich_channel_rest_post',
            [],
            'POST',
            [],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    private function findChannel(string $code): ?ChannelInterface
    {
        $this->clearDoctrineUoW();

        return $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);
    }

    /**
     * @return string[]
     */
    private function currencyCodesOf(ChannelInterface $channel): array
    {
        $codes = [];
        foreach ($channel->getCurrencies() as $currency) {
            $codes[] = $currency->getCode();
        }

        return $codes;
    }

    /**
     * @return string[]
     */
    private function activatedLocaleCodes(): array
    {
        $this->clearDoctrineUoW();

        return $this->get('pim_catalog.repository.locale')->getActivatedLocaleCodes();
    }

    /**
     * @return string[]
     */
    private function activatedCurrencyCodes(): array
    {
        $this->clearDoctrineUoW();

        return $this->get('pim_catalog.repository.currency')->getActivatedCurrencyCodes();
    }
}
