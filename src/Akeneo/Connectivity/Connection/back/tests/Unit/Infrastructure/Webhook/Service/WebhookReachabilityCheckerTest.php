<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookReachabilityCheckerTest extends TestCase
{
    private ClientInterface|MockObject $client;
    private ValidatorInterface|MockObject $validator;
    private VersionProviderInterface|MockObject $versionProvider;
    private WebhookReachabilityChecker $sut;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->versionProvider = $this->createMock(VersionProviderInterface::class);
        $this->sut = new WebhookReachabilityChecker($this->client, $this->validator, $this->versionProvider, \getenv('PFID'));
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UrlReachabilityCheckerInterface::class, $this->sut);
    }

    public function test_it_checks_url_is_good_and_reachable(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $this->client->method('send')->with($this->callback(fn ($object): bool => $object instanceof Request
                    && $object->hasHeader('Content-Type')
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)
                    && $this::POST === $object->getMethod()
                    && $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willReturn(new Response(200, [], null, '1.1', 'OK'));
        $this->validator->method('validate')->with($validUrl, $this->anything())->willReturn(new ConstraintViolationList());
        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(true, "200 OK")
        );
    }

    public function test_it_checks_url_has_invalid_format(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $notValidUrl = 'I_AM_NOT_A_VALID_URL';
        $secret = '1234';
        $violationList = new ConstraintViolationList([$violation]);
        $violation->method('getMessage')->willReturn($notValidUrl);
        $this->validator->method('validate')->with(
            $notValidUrl,
            $this->anything()
        )->willReturn($violationList);
        $resultUrlReachabilityStatus = $this->check($notValidUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(false, $notValidUrl)
        );
    }

    public function test_it_checks_url_has_invalid_format_because_url_is_blank(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $emptyUrl = '';
        $secret = '1234';
        $violationList = new ConstraintViolationList([$violation]);
        $violation->method('getMessage')->willReturn($emptyUrl);
        $this->validator->method('validate')->with(
            $emptyUrl,
            $this->anything()
        )->willReturn($violationList);
        $resultUrlReachabilityStatus = $this->check($emptyUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(false, $emptyUrl)
        );
    }

    public function test_it_checks_url_is_good_and_reachable_but_have_301_redirect_response(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $this->client->method('send')->with($this->callback(fn ($object): bool => $object instanceof Request
                    && $object->hasHeader('Content-Type')
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)
                    && $this::POST === $object->getMethod()
                    && $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willReturn(new Response(301, [], null, '1.1', 'Moved Permanently'));
        $this->validator->method('validate')->with($validUrl, $this->anything())->willReturn(new ConstraintViolationList());
        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(false, '301 Server response contains a redirection. This is not allowed.')
        );
    }

    public function test_it_checks_url_is_not_reachable_and_has_response(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $request = new Request($this::POST, $validUrl, []);
        $response = new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons');
        $requestException = new RequestException('RequestException message', $request, $response);
        $this->client->method('send')->with($this->callback(fn ($object): bool => $object instanceof Request
                    && $object->hasHeader('Content-Type')
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)
                    && $this::POST === $object->getMethod()
                    && $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willThrowException($requestException);
        $this->validator->method('validate')->with($validUrl, $this->anything())->willReturn(new ConstraintViolationList());
        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(false, "451 Unavailable For Legal Reasons")
        );
    }

    public function test_it_checks_url_is_not_reachable_and_has_no_response(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $request = new Request($this::POST, $validUrl, []);
        $connectException = new ConnectException('ConnectException message', $request);
        $this->client->method('send')->with($this->callback(fn ($object): bool => $object instanceof Request
                    && $object->hasHeader('Content-Type')
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)
                    && $this::POST === $object->getMethod()
                    && $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willThrowException($connectException);
        $this->validator->method('validate')->with($validUrl, $this->anything())->willReturn(new ConstraintViolationList());
        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(false, "Failed to connect to server")
        );
    }

    public function test_it_checks_url_is_not_reachable_and_no_request_exception_has_been_raised(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $transferException = new TransferException('TransferException message');
        $this->client->method('send')->with($this->callback(fn ($object): bool => $object instanceof Request
                    && $object->hasHeader('Content-Type')
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)
                    && $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)
                    && $this::POST === $object->getMethod()
                    && $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willThrowException($transferException);
        $this->validator->method('validate')->with($validUrl, $this->anything())->willReturn(new ConstraintViolationList());
        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);
        Assert::assertEquals(
            $resultUrlReachabilityStatus,
            new UrlReachabilityStatus(false, "Failed to connect to server")
        );
    }
}
