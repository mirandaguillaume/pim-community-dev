<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\ErrorHandling;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Guards the two settings that together keep a PHP warning fatal for the whole unit suite.
 * Neither had a test before this one, so deleting either silently brought back a suite that
 * can assert nothing: a warning became a log line PHPUnit never sees, and an undefined
 * variable cost nothing.
 *
 * - `config/bootstrap.php` registers Symfony's ErrorHandler *before* PHPUnit installs its own.
 *   PHPUnit 10 masks E_WARNING and E_NOTICE out of `error_reporting()` unless it finds a handler
 *   already in place.
 * - `config/packages/test_fake/framework.yml` sets `php_errors.throw: true`. The container is
 *   built with debug off, so without it FrameworkBundle's ErrorHandlerConfigurator calls
 *   `throwAt(0)` on that same handler from the first kernel boot onward.
 *
 * Both are required, which is why the warning is raised *after* a kernel boot: that boot is the
 * moment the reconfiguration happens, so a test asserting before it would pass either way.
 */
final class PhpWarningsAreFatalTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        // Named here rather than taken from KERNEL_CLASS. Infection generates its own PHPUnit
        // configuration and drops the `<server name="KERNEL_CLASS">` entry phpunit.xml.dist
        // sets, so depending on that variable makes this test error under the mutation job,
        // which then reports "Project tests must be in a passing state before running
        // Infection" and fails the whole scope rather than this one test.
        return \Kernel::class;
    }

    public function test_a_php_warning_raised_after_a_kernel_boot_is_thrown(): void
    {
        static::bootKernel();

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessageMatches('/failed to open stream/i');

        \fopen(__DIR__ . '/a-path-that-does-not-exist', 'r');
    }
}
