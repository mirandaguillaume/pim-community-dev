<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Token holding system user info.
 * A system user has no password, no credentials and is authenticated by default.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SystemUserToken extends AbstractToken
{
    /**
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        parent::__construct($user->getRoles());
        $this->setUser($user);
        $this->setAuthenticated(true);
    }

    /**
     * @deprecated since Symfony 5.4, will be removed in Symfony 6.0
     */
    public function getCredentials()
    {
        return null;
    }
}
