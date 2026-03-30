<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EnabledWebhookRequiresAnUrl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class EnabledWebhookRequiresAnUrlTest extends TestCase
{
    private EnabledWebhookRequiresAnUrl $sut;

    protected function setUp(): void
    {
        $this->sut = new EnabledWebhookRequiresAnUrl();
    }

    public function test_it_is_an_enabled_webhook_requires_an_url_constraint(): void
    {
        $this->assertInstanceOf(EnabledWebhookRequiresAnUrl::class, $this->sut);
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_provides_targets(): void
    {
        $this->assertSame(EnabledWebhookRequiresAnUrl::CLASS_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_provides_a_message(): void
    {
        $this->assertSame('akeneo_connectivity.connection.webhook.error.required', $this->sut->message);
    }
}
