<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\UserManagement\Bundle\Notification\MailResetNotifier;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class MailResetNotifierTest extends TestCase
{
    private MailResetNotifier $sut;

    protected function setUp(): void
    {
        $this->sut = new MailResetNotifier();
    }

    private function given(User $user, Environment $twig)
    {
            $user->getEmail()->willReturn('email');
            $twig->render('@PimUser/Mail/reset.txt.twig', ['user' => $user])->willReturn('textBody');
            $twig->render('@PimUser/Mail/reset.html.twig', ['user' => $user])->willReturn('htmlBody');
        }
}
