<?php

namespace Akeneo\UserManagement\Bundle\Form\Handler;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Overridden ResetHandler in order to manage the reset password
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetHandler
{
    public function __construct(private readonly FormInterface $form, private readonly RequestStack $requestStack, private readonly UserManager $manager) {}

    public function process(UserInterface $user): bool
    {
        $this->form->setData($user);
        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->handleRequest($this->getRequest());
            if ($this->form->isValid()) {
                $this->onSuccess($user);
                return true;
            }
        }
        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $user
            ->setPlainPassword($this->form->getData()->getPlainPassword())
            ->setConfirmationToken(null)
            ->setPasswordRequestedAt(null);

        $this->manager->updateUser($user);
    }

    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
