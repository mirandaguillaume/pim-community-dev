<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class BehatCoverageSubscriberTest extends TestCase
{
    public function test_it_subscribes_to_the_kernel_request_event(): void
    {
        self::assertArrayHasKey(KernelEvents::REQUEST, BehatCoverageSubscriber::getSubscribedEvents());
    }

    public function test_it_starts_collection_on_a_main_request_when_enabled(): void
    {
        $collector = new SpyCollector();
        $subscriber = new BehatCoverageSubscriber(true, '/tmp/whatever', $collector);

        $subscriber->onRequest($this->mainRequestEvent());

        self::assertSame(1, $collector->startCalls);
    }

    public function test_it_does_nothing_when_disabled(): void
    {
        $collector = new SpyCollector();
        $subscriber = new BehatCoverageSubscriber(false, '/tmp/whatever', $collector);

        $subscriber->onRequest($this->mainRequestEvent());

        self::assertSame(0, $collector->startCalls);
    }

    public function test_it_ignores_sub_requests(): void
    {
        $collector = new SpyCollector();
        $subscriber = new BehatCoverageSubscriber(true, '/tmp/whatever', $collector);

        $subscriber->onRequest($this->subRequestEvent());

        self::assertSame(0, $collector->startCalls);
    }

    private function mainRequestEvent(): RequestEvent
    {
        return new RequestEvent($this->kernel(), new Request(), HttpKernelInterface::MAIN_REQUEST);
    }

    private function subRequestEvent(): RequestEvent
    {
        return new RequestEvent($this->kernel(), new Request(), HttpKernelInterface::SUB_REQUEST);
    }

    private function kernel(): HttpKernelInterface
    {
        return new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): \Symfony\Component\HttpFoundation\Response
            {
                return new \Symfony\Component\HttpFoundation\Response();
            }
        };
    }
}

final class SpyCollector implements CoverageCollectorInterface
{
    public int $startCalls = 0;

    public function start(): void
    {
        $this->startCalls++;
    }

    public function stopAndDump(string $dir): void
    {
    }
}
