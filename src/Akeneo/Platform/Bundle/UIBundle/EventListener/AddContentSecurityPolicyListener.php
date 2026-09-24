<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Inject CSP headers in response object
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: KernelEvents::RESPONSE, method: 'addCspHeaders')]
class AddContentSecurityPolicyListener
{
    /**
     * No `includeSubDomains`: this is Community Edition, installed by third parties on their own
     * hostnames. Asserting HSTS for every sibling subdomain of an operator's domain is their call to
     * make at the reverse proxy, not a default this application may impose on them.
     */
    private const STRICT_TRANSPORT_SECURITY = 'max-age=31536000';

    /**
     * Deny the powerful features nothing in this application uses. Verified absent from src/,
     * front-packages/ and components/: getUserMedia, mediaDevices, navigator.geolocation,
     * requestFullscreen, PaymentRequest, navigator.usb, DeviceOrientation.
     * `clipboard-write` is deliberately NOT denied — media-url-generator.ts and
     * SftpStorageConfigurator.tsx both call navigator.clipboard.writeText().
     */
    private const PERMISSIONS_POLICY = 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()';

    public function __construct(private readonly ContentSecurityPolicyProvider $contentSecurityPolicyProvider)
    {
    }

    public function addCspHeaders(ResponseEvent $event): void
    {
        $policy = $this->contentSecurityPolicyProvider->getPolicy();

        $response = $event->getResponse();
        $response->headers->set('Content-Security-Policy', $policy);
        $response->headers->set('X-Content-Security-Policy', $policy);
        $response->headers->set('X-WebKit-CSP', $policy);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', self::PERMISSIONS_POLICY);

        // RFC 6797 section 7.2: an HSTS host MUST NOT send the field over non-secure transport.
        // Request::isSecure() honours X-Forwarded-Proto only for proxies declared in
        // TRUSTED_PROXY_IPS (public/index.php), so behind an untrusted proxy this header is simply
        // never sent — which is the safe direction to fail in.
        if ($event->getRequest()->isSecure()) {
            $response->headers->set('Strict-Transport-Security', self::STRICT_TRANSPORT_SECURITY);
        }
    }
}
