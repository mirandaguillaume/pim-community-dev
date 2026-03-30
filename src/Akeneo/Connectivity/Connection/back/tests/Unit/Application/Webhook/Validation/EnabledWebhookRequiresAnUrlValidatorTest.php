<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EnabledWebhookRequiresAnUrl;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EnabledWebhookRequiresAnUrlValidator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EnabledWebhookRequiresAnUrlValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private EnabledWebhookRequiresAnUrlValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new EnabledWebhookRequiresAnUrlValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_an_enabled_webhook_requires_an_url_constraint_validator(): void
    {
        $this->assertInstanceOf(EnabledWebhookRequiresAnUrlValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_validates_an_enabled_webhook_with_an_url(): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true, 'http://valid-url.com');
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_validates_a_disabled_webhook_with_an_url(): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', false, 'http://valid-url.com');
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_validates_a_disabled_webhook_with_an_empty_url(): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', false, '');
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_validates_a_disabled_webhook_with_a_null_url(): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', false);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_validates_an_enabled_webhook_with_an_invalid_url(): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true, 'not_a_url');
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_does_not_validate_an_enabled_webhook_with_a_null_url(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('url')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_does_not_validate_an_enabled_webhook_with_an_empty_url(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true, '');
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('url')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($webhook, $constraint);
    }

    public function test_it_throws_an_exception_if_the_given_constraint_is_not_the_good_one(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate(
            new ConnectionWebhook('magento', false),
            new LocalConstraint(),
        );
    }

    public function test_it_throws_an_exception_if_the_data_to_validate_is_not_connection_webhook_write_model(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate(
            'a_webhook',
            new EnabledWebhookRequiresAnUrl(),
        );
    }
}
