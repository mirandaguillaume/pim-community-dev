<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::getContainer()->get('test.client');
    }

    protected function authenticateAsAdmin(): UserInterface
    {
        $user = $this->createAdminUser();

        $this->client->loginUser($user, 'main');

        return $user;
    }
}
