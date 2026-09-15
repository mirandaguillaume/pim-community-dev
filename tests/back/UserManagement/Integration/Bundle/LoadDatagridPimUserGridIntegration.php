<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the System > Users grid. datagrid/user.yml restricts pim-user-grid to UI users
 * (u.type = TYPE_USER) and sorts it by username, so connection (API) and job users must
 * never show up there, where they could be edited or deleted.
 * Backend guard for the deleted Behat scenario browse_users.feature:12.
 */
final class LoadDatagridPimUserGridIntegration extends ControllerIntegrationTestCase
{
    public function test_it_lists_enabled_and_disabled_ui_users_sorted_by_username_and_hides_api_and_job_users(): void
    {
        // Created in a non-alphabetical order so that the default username ASC sort is observable.
        $zoe = $this->createUserConfiguredWith('zoe', static fn (User $user) => $user->setEnabled(false));
        $mary = $this->createUserConfiguredWith('mary', static fn (User $user) => $user->defineAsUiUser());
        $this->createUserConfiguredWith('magento_connection_0001', static fn (User $user) => $user->defineAsApiUser());
        $this->createUserConfiguredWith('job_automated_export', static fn (User $user) => $user->defineAsJobUser());

        $this->logIn('admin');
        $response = $this->callApiRoute('pim_datagrid_load', ['alias' => 'pim-user-grid']);

        $this->assertStatusCode($response, Response::HTTP_OK);
        $content = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $grid = \json_decode((string) $content['data'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['admin', 'mary', 'zoe'], \array_column($grid['data'], 'username'));
        self::assertEquals(3, $grid['options']['totalRecords']);

        $rowsByUsername = \array_column($grid['data'], null, 'username');
        self::assertEquals(true, $rowsByUsername['mary']['enabled']);
        self::assertEquals(false, $rowsByUsername['zoe']['enabled']);
        self::assertSame((string) $zoe->getId(), (string) $rowsByUsername['zoe']['id']);
        self::assertSame(
            $this->router->generate('pim_user_edit', ['identifier' => $mary->getId()]),
            $rowsByUsername['mary']['update_link']
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param \Closure(User): mixed $configure
     */
    private function createUserConfiguredWith(string $username, \Closure $configure): User
    {
        $user = $this->createUser($username);
        $configure($user);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
