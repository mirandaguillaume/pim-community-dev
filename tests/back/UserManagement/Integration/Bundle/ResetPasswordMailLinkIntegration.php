<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The password-reset mail carries the reset token in a link. It used to be built with Twig's url(),
 * whose scheme and host come from the router context, i.e. from the Host header of the request
 * that asked for the reset (pim_user_reset_send_email is unauthenticated). Without
 * framework.trusted_hosts, anyone could ask a reset for a victim with "Host: evil.tld" and have the
 * victim mailed a link -- token included -- to that domain.
 *
 * Rendering needs the kernel only, no catalog: no database or search index is touched.
 *
 * The link is now built from AKENEO_PIM_URL, like the job notification mail. Setting the router
 * context host is exactly what a forged Host header does to url() during the real request.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ResetPasswordMailLinkIntegration extends KernelTestCase
{
    private const FORGED_HOST = 'evil.example';
    private const TOKEN = 'a1b2c3d4e5f6';

    public function test_the_reset_links_ignore_the_request_host(): void
    {
        $container = static::getContainer();
        $twig = $container->get('twig');
        $context = $container->get('router')->getContext();
        $context->setScheme('https');
        $context->setHost(self::FORGED_HOST);

        $user = new User();
        $user->setUsername('reset_link_target');
        $user->setEmail('reset_link_target@example.com');
        $user->setConfirmationToken(self::TOKEN);

        $pimUrl = (string) $twig->getGlobals()['akeneo_pim_url'];
        Assert::assertNotSame('', $pimUrl, 'AKENEO_PIM_URL must be set for this test to mean anything');
        $expected = rtrim($pimUrl, '/') . '/user/reset/' . self::TOKEN;

        foreach (['@PimUser/Mail/reset.html.twig', '@PimUser/Mail/reset.txt.twig'] as $template) {
            $body = $twig->render($template, ['user' => $user]);

            Assert::assertStringNotContainsString(self::FORGED_HOST, $body, sprintf('%s links to the request host', $template));
            Assert::assertStringContainsString($expected, $body, sprintf('%s does not link to AKENEO_PIM_URL', $template));
        }
    }
}
