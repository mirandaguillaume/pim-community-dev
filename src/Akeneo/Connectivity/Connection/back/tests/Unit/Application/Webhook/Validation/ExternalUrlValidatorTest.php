<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\IpMatcherInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ExternalUrl;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ExternalUrlValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExternalUrlValidatorTest extends TestCase
{
    private DnsLookupInterface|MockObject $dnsLookup;
    private IpMatcherInterface|MockObject $ipMatcher;
    private ExternalUrlValidator $sut;

    protected function setUp(): void
    {
        $this->dnsLookup = $this->createMock(DnsLookupInterface::class);
        $this->ipMatcher = $this->createMock(IpMatcherInterface::class);
        $this->sut = new ExternalUrlValidator($this->dnsLookup, $this->ipMatcher);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ExternalUrlValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_does_not_support_other_constraints(): void
    {
        $constraint = $this->createMock(Constraint::class);

        $this->expectException(new UnexpectedTypeException($constraint->getWrappedObject(), ExternalUrl::class));
        $this->sut->validate('', $constraint);
    }

    public function test_it_ignores_the_value_if_empty(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = '';
        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_ignores_the_value_if_not_an_url(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'not_an_url';
        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_ignores_the_value_if_url_cannot_be_resolved(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'http://akeneo.com/foo';
        $this->dnsLookup->method('ip')->with('akeneo.com')->willReturn(null);
        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_adds_a_violation_if_the_url_is_localhost(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'http://localhost/foo';
        $context->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }

    public function test_it_adds_a_violation_if_the_url_is_elasticsearch(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'http://elasticsearch/foo';
        $context->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }

    public function test_it_adds_a_violation_if_the_url_is_memcached(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'http://memcached/foo';
        $context->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }

    public function test_it_adds_a_violation_if_the_ip_is_in_private_range(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'http://akeneo.com/foo';
        $this->dnsLookup->method('ip')->with('akeneo.com')->willReturn('172.16.0.1');
        $context->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }

    public function test_it_allows_the_ip_if_in_private_range_and_in_whitelist(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut = new ExternalUrlValidator($this->dnsLookup, $this->ipMatcher, '172.16.0.0/24');
        $this->sut->initialize($context);
        $value = 'http://akeneo.com/foo';
        $this->dnsLookup->method('ip')->with('akeneo.com')->willReturn('172.16.0.1');
        $this->ipMatcher->method('match')->with('172.16.0.1', ['172.16.0.0/24'])->willReturn(true);
        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_allows_the_ip_if_external(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'http://akeneo.com/foo';
        $this->dnsLookup->method('ip')->with('akeneo.com')->willReturn('168.212.226.204');
        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_denies_localhost_ip(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = $this->createMock(ExternalUrl::class);

        $this->sut->initialize($context);
        $value = 'https://127.0.0.1/foo';
        $this->dnsLookup->method('ip')->with('127.0.0.1')->willReturn('127.0.0.1');
        $context->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }
}
