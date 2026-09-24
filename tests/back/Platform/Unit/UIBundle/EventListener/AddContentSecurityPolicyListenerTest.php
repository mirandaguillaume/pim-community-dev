<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\UIBundle\EventListener;

use Akeneo\Platform\Bundle\UIBundle\EventListener\AddContentSecurityPolicyListener;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The listener is the only place this application sets response security headers: docker/akeneo.conf
 * is a bare VirtualHost with no Header directives, and no other class in the repository emits any of
 * these four names.
 *
 * ResponseEvent and ContentSecurityPolicyProvider are both final, so both are constructed rather than
 * mocked.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddContentSecurityPolicyListenerTest extends TestCase
{
    private const STRICT_TRANSPORT_SECURITY = 'max-age=31536000';

    public function test_it_sets_the_transport_agnostic_security_headers(): void
    {
        $response = $this->handle(Request::create('http://akeneo.test/'));

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertStringContainsString('camera=()', (string) $response->headers->get('Permissions-Policy'));
        $this->assertStringContainsString('microphone=()', (string) $response->headers->get('Permissions-Policy'));
    }

    /**
     * clipboard-write must stay allowed: media-url-generator.ts and SftpStorageConfigurator.tsx both
     * call navigator.clipboard.writeText(), which a clipboard-write=() policy would break silently.
     */
    public function test_it_does_not_deny_clipboard_write(): void
    {
        $response = $this->handle(Request::create('http://akeneo.test/'));

        // Anchor the presence first: without it this assertion passes on an absent header, i.e. it
        // would stay green with no implementation at all.
        $this->assertTrue($response->headers->has('Permissions-Policy'));
        $this->assertStringNotContainsString('clipboard', (string) $response->headers->get('Permissions-Policy'));
    }

    /**
     * RFC 6797 section 7.2: an HSTS host MUST NOT send the field over non-secure transport.
     */
    public function test_it_does_not_advertise_hsts_over_plain_http(): void
    {
        $response = $this->handle(Request::create('http://akeneo.test/'));

        $this->assertFalse($response->headers->has('Strict-Transport-Security'));
    }

    public function test_it_advertises_hsts_over_https(): void
    {
        $response = $this->handle(Request::create('https://akeneo.test/'));

        $this->assertSame(self::STRICT_TRANSPORT_SECURITY, $response->headers->get('Strict-Transport-Security'));
    }

    /**
     * The supported production topology terminates TLS at a reverse proxy, so isSecure() has to come
     * from X-Forwarded-Proto — which Symfony honours only for proxies listed in TRUSTED_PROXY_IPS.
     */
    public function test_it_advertises_hsts_behind_a_tls_terminating_trusted_proxy(): void
    {
        $request = Request::create('http://akeneo.test/', server: ['REMOTE_ADDR' => '10.0.0.1']);
        $request->headers->set('X-Forwarded-Proto', 'https');

        Request::setTrustedProxies(['10.0.0.1'], Request::HEADER_X_FORWARDED_PROTO);
        try {
            $response = $this->handle($request);
        } finally {
            // phpunit.xml.dist sets processIsolation="false", so this static would leak into every
            // later test in the suite.
            Request::setTrustedProxies([], 0);
        }

        $this->assertSame(self::STRICT_TRANSPORT_SECURITY, $response->headers->get('Strict-Transport-Security'));
    }

    /**
     * The CSP headers this listener already owned must keep being set even when no provider
     * contributes a directive, otherwise a misconfiguration would silently drop them.
     */
    public function test_it_still_sets_the_content_security_policy_headers(): void
    {
        $response = $this->handle(Request::create('http://akeneo.test/'));

        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertTrue($response->headers->has('X-Content-Security-Policy'));
        $this->assertTrue($response->headers->has('X-WebKit-CSP'));
    }

    private function handle(Request $request): Response
    {
        $listener = new AddContentSecurityPolicyListener(new ContentSecurityPolicyProvider([]));
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );

        $listener->addCspHeaders($event);

        return $event->getResponse();
    }
}
