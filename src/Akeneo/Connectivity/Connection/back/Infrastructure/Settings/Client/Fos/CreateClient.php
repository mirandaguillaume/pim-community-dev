<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Settings\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client as ApiClient;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\OAuth2;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateClient implements CreateClientInterface
{
    public function __construct(private readonly ClientManagerInterface $clientManager) {}

    public function execute(string $label): Client
    {
        /** @var ApiClient */
        $fosClient = $this->clientManager->createClient();

        $fosClient->setLabel($label);
        $fosClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);

        $this->clientManager->updateClient($fosClient);

        return new Client($fosClient->getId(), $fosClient->getPublicId(), $fosClient->getSecret());
    }
}
