<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Starts PCOV line coverage on each main request and dumps it on shutdown.
 * Registered only in APP_ENV=behat; a no-op unless PCOV is enabled (nightly),
 * so normal behat runs pay nothing. Best-effort — never breaks a scenario.
 */
final class BehatCoverageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $dumpDir,
        private readonly ?CoverageCollectorInterface $collector = null,
    ) {
    }

    public static function fromEnvironment(string $dumpDir): self
    {
        $enabled = \extension_loaded('pcov') && (int) \ini_get('pcov.enabled') === 1;

        return new self($enabled, $dumpDir);
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 1024]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->enabled || !$event->isMainRequest()) {
            return;
        }

        try {
            $collector = $this->collector ?? CoverageCollector::create();
            $collector->start();

            $dir = $this->dumpDir;
            // php-fpm reuses worker processes, but each request boots a fresh kernel and
            // fires+clears its own shutdown callbacks, so start()+dump pair once per request.
            \register_shutdown_function(static function () use ($collector, $dir): void {
                try {
                    $collector->stopAndDump($dir);
                } catch (\Throwable) {
                    // best-effort: a coverage dump must never affect the request outcome
                }
            });
        } catch (\Throwable) {
            // best-effort: never break a scenario if a driver is unexpectedly missing
        }
    }
}
