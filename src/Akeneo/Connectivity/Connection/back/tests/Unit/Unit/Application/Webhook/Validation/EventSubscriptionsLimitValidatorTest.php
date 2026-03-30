<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EventSubscriptionsLimit;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EventSubscriptionsLimitValidator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EventSubscriptionsLimitValidatorTest extends TestCase
{
    private SelectActiveWebhooksQueryInterface|MockObject $selectActiveWebhooksQuery;
    private ExecutionContextInterface|MockObject $context;
    private EventSubscriptionsLimitValidator $sut;

    protected function setUp(): void
    {
        $this->selectActiveWebhooksQuery = $this->createMock(SelectActiveWebhooksQueryInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new EventSubscriptionsLimitValidator($this->selectActiveWebhooksQuery, self::ACTIVE_EVENT_SUBSCRIPTIONS_LIMIT);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventSubscriptionsLimitValidator::class, $this->sut);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_supports_the_event_subscriptions_limit_constraint(): void
    {
        $this->selectActiveWebhooksQuery->method('execute')->willReturn([]);
        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();
        $this->sut->shouldNotThrow(new UnexpectedTypeException($constraint, EventSubscriptionsLimit::class))
                    ->during('validate', [$eventSubscription, $constraint]);
    }

    public function test_it_does_not_support_other_constraints(): void
    {
        $constraint = $this->createMock(Constraint::class);

        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $this->expectException(new UnexpectedTypeException($constraint->getWrappedObject(), EventSubscriptionsLimit::class));
        $this->sut->validate($eventSubscription, $constraint);
    }

    public function test_it_supports_the_event_subscription_value(): void
    {
        $this->selectActiveWebhooksQuery->method('execute')->willReturn([]);
        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();
        $this->sut->shouldNotThrow(new UnexpectedValueException($eventSubscription, ConnectionWebhook::class))
                    ->during('validate', [$eventSubscription, $constraint]);
    }

    public function test_it_does_not_support_other_values(): void
    {
        $value = new \stdClass();
        $constraint = new EventSubscriptionsLimit();
        $this->expectException(new UnexpectedValueException($value, ConnectionWebhook::class));
        $this->sut->validate($value, $constraint);
    }

    public function test_it_does_not_check_the_limit_if_the_event_subscription_is_disabled(): void
    {
        $this->selectActiveWebhooksQuery->expects($this->never())->method('execute');
        $eventSubscription = new ConnectionWebhook('erp', false, null);
        $constraint = new EventSubscriptionsLimit();
        $this->sut->validate($eventSubscription, $constraint);
    }

    public function test_it_adds_a_violation_if_the_limit_is_reached(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->selectActiveWebhooksQuery->method('execute')->willReturn([
                    new ActiveWebhook('dam', 1, 'secret', 'http://localhost', false),
                    new ActiveWebhook('ecommerce', 1, 'secret', 'http://localhost', false),
                    new ActiveWebhook('translations', 1, 'secret', 'http://localhost', false),
                ]);
        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();
        $this->context->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('atPath')->with('enabled')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($eventSubscription, $constraint);
    }

    public function test_it_does_not_count_itself_in_the_limit_check(): void
    {
        $this->selectActiveWebhooksQuery->method('execute')->willReturn([
                    new ActiveWebhook('dam', 1, 'secret', 'http://localhost', false),
                    new ActiveWebhook('ecommerce', 1, 'secret', 'http://localhost', false),
                    new ActiveWebhook('erp', 1, 'secret', 'http://localhost', false),
                ]);
        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();
        $this->context->expects($this->never())->method('buildViolation')->with($constraint->message);
        $this->sut->validate($eventSubscription, $constraint);
    }
}
