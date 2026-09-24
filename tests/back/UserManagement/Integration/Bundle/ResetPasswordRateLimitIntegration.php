<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * pim_user_reset_send_email is unauthenticated and, before this, unthrottled: it was both a free
 * mail relay and — because its outcomes differed — an account-existence oracle.
 *
 * Two properties are asserted here, and the second is the one easy to get wrong:
 *   1. the endpoint stops accepting requests past the configured limit;
 *   2. every non-throttled outcome returns the SAME BODY, so no response distinguishes a known
 *      address from an unknown one. Comparing status codes alone would pass even if the bodies
 *      differed, which is exactly the leak.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ResetPasswordRateLimitIntegration extends ControllerIntegrationTestCase
{
    /** Must match framework.yml's rate_limiter.reset_password.limit. */
    private const LIMIT = 20;

    protected function setUp(): void
    {
        parent::setUp();
        // Every WebTestCase request comes from 127.0.0.1 and the bucket is a filesystem cache
        // pool that survives between test classes in a shard, so start from a known state.
        $this->get('test.limiter.reset_password')->create('127.0.0.1')->reset();
    }

    public function test_it_rejects_requests_past_the_limit(): void
    {
        for ($i = 0; $i < self::LIMIT; $i++) {
            $response = $this->sendEmailFor('nobody@example.com');
            Assert::assertSame(
                Response::HTTP_OK,
                $response->getStatusCode(),
                \sprintf('Request %d of %d was rejected before the limit', $i + 1, self::LIMIT)
            );
        }

        Assert::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $this->sendEmailFor('nobody@example.com')->getStatusCode());
    }

    public function test_a_known_address_is_indistinguishable_from_an_unknown_one(): void
    {
        // The user must really exist, or both requests take the unknown branch and this test
        // passes without ever reaching the code it is meant to pin.
        $this->createUser('oracle_target');

        $known = $this->sendEmailFor('oracle_target');
        $unknown = $this->sendEmailFor('no-such-user@example.com');

        Assert::assertSame($unknown->getStatusCode(), $known->getStatusCode());
        Assert::assertSame(
            $unknown->getContent(),
            $known->getContent(),
            'The response for a known account differs from the one for an unknown address, which makes this endpoint an account-existence oracle'
        );
    }

    /**
     * The second request within the TTL used to answer 302 with a "already been requested within
     * the last 24 hours" flash, while an unknown address got 200 — the leak this closes. The first
     * call below is what sets passwordRequestedAt, so the second really does hit that branch.
     */
    public function test_a_repeated_request_is_indistinguishable_from_an_unknown_one(): void
    {
        $this->createUser('oracle_repeat');

        $first = $this->sendEmailFor('oracle_repeat');
        Assert::assertSame(Response::HTTP_OK, $first->getStatusCode());

        $repeat = $this->sendEmailFor('oracle_repeat');
        $unknown = $this->sendEmailFor('no-such-user@example.com');

        Assert::assertSame($unknown->getStatusCode(), $repeat->getStatusCode());
        Assert::assertSame(
            $unknown->getContent(),
            $repeat->getContent(),
            'A repeated reset request answers differently from an unknown address, which reveals that the account exists'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function sendEmailFor(string $username): Response
    {
        return $this->callRoute('pim_user_reset_send_email', [], 'POST', [], ['username' => $username]);
    }
}
