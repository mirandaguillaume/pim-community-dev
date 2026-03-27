<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\AkeneoContentSecurityPolicyProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoContentSecurityPolicyProviderTest extends TestCase
{
    private AkeneoContentSecurityPolicyProvider $sut;

    protected function setUp(): void {}

    public function test_it_returns_the_akeneo_policy(): void
    {
        $nonceGenerator = $this->createMock(ScriptNonceGenerator::class);

        $nonceGenerator->expects($this->once())->method('getGeneratedNonce')->willReturn('thisisarandomhash');
        $this->sut = new AkeneoContentSecurityPolicyProvider($nonceGenerator, 'trusted-domain.com');
        $this->assertSame([
            'default-src'
                => [
                    "'self'",
                    "'unsafe-inline'",
                ],
            'script-src'
                => [
                    "'self'",
                    "'unsafe-eval'",
                    "'nonce-thisisarandomhash'",
                ],
            'img-src'
                => [
                    "'self'",
                    'data:',
                ],
            'frame-src'
                => [
                    "'self'",
                ],
            'font-src'
                => [
                    "'self'",
                    'data:',
                ],
            'connect-src'
                => [
                    "'self'",
                    "updates.akeneo.com",
                ],
            'style-src'
                => [
                    "'self'",
                    "'unsafe-inline'",
                ],
            'frame-ancestors'
                => [
                    "'self'",
                ],
        ], $this->sut->getContentSecurityPolicy());
    }
}
