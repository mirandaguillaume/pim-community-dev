<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\UserManagement\Bundle\Form\Handler\ResetHandler;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Notification\MailResetNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResetController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly RequestStack $requestStack,
        private readonly ResetHandler $resetHandler,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FormInterface $form,
        private readonly MailResetNotifier $mailer,
        private readonly RateLimiterFactory $resetPasswordLimiter,
    ) {
    }

    public function request(): Response
    {
        return $this->render('@PimUser/Reset/request.html.twig');
    }

    /**
     * Request reset user password
     */
    public function sendEmail(Request $request): Response
    {
        // Throttle before the lookup, so a rejected request costs no database work and reveals
        // nothing about the submitted address.
        $limiter = $this->resetPasswordLimiter->create($request->getClientIp() ?? 'unknown');
        if (false === $limiter->consume()->isAccepted()) {
            // Same body as every other outcome of this endpoint: only the status differs, so a
            // throttled response still cannot tell an existing account from an unknown one.
            return $this->render(
                '@PimUser/Reset/sendEmail.html.twig',
                [],
                new Response('', Response::HTTP_TOO_MANY_REQUESTS)
            );
        }

        $username = $request->request->get('username');
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (null === $user || false === $user->isEnabled()) {
            return $this->render('@PimUser/Reset/sendEmail.html.twig');
        }

        if ($user->isPasswordRequestNonExpired($this->getParameter('pim_user.reset.ttl'))) {
            // Do not send a second mail, and do not say so. The previous 302-with-a-warning
            // differed from the 200 an unknown address gets, which made this endpoint an
            // account-existence oracle for anyone willing to submit one address at a time.
            return $this->render('@PimUser/Reset/sendEmail.html.twig');
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->userManager->updateUser($user);

        $this->mailer->notify($user);

        return $this->render('@PimUser/Reset/sendEmail.html.twig');
    }

    /**
     * Reset user password
     */
    public function reset(string $token): Response
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user || false === $user->isEnabled()) {
            throw $this->createNotFoundException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired($this->getParameter('pim_user.reset.ttl'))) {
            $this->addFlash(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirectToRoute('pim_user_reset_request');
        }

        if ($this->resetHandler->process($user)) {
            $this->addFlash('success', 'Your password has been successfully reset. You may login now.');

            // force user logout
            $this->requestStack->getSession()->invalidate();
            $this->tokenStorage->setToken(null);

            return $this->redirectToRoute('pim_user_security_login');
        }

        return $this->render('@PimUser/Reset/reset.html.twig', [
            'token' => $token,
            'form' => $this->form,
        ]);
    }
}
