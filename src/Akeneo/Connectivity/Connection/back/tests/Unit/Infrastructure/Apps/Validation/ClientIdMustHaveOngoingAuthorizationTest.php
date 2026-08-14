<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustHaveOngoingAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustHaveOngoingAuthorizationValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientIdMustHaveOngoingAuthorizationTest extends TestCase
{
    private ClientIdMustHaveOngoingAuthorization $sut;

    protected function setUp(): void
    {
        $this->sut = new ClientIdMustHaveOngoingAuthorization();
    }

    public function test_it_is_a_symfony_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_targets_a_property(): void
    {
        $this->assertSame(Constraint::PROPERTY_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_can_be_mapped_on_the_client_id_property_of_the_create_connected_app_command(): void
    {
        $metadata = new ClassMetadata(CreateConnectedAppWithAuthorizationCommand::class);
        $metadata->addPropertyConstraint('clientId', $this->sut);

        $propertyMetadata = $metadata->getPropertyMetadata('clientId');

        $this->assertCount(1, $propertyMetadata);
        $this->assertSame([$this->sut], $propertyMetadata[0]->getConstraints());
    }

    public function test_it_is_validated_by_the_validator_matching_its_own_name(): void
    {
        $validator = $this->sut->validatedBy();
        $this->assertSame(ClientIdMustHaveOngoingAuthorizationValidator::class, $validator);
        // `Foo::class` is resolved lexically and never autoloads, so the assertion above
        // compares two spellings of the same naming convention: rename or delete the
        // validator and it still passes. Verified by mutation -- the class was renamed
        // outright and the suite stayed green. This is what makes it bite.
        $this->assertTrue(class_exists($validator), "ClientIdMustHaveOngoingAuthorizationValidator must actually exist");
    }

    public function test_it_exposes_a_default_message(): void
    {
        $this->assertSame('Client ID must have an ongoing authorization', $this->sut->message);
    }

    public function test_it_lets_the_validation_mapping_override_the_message(): void
    {
        $constraint = new ClientIdMustHaveOngoingAuthorization([
            'message' => 'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
        ]);

        $this->assertSame(
            'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
            $constraint->message,
        );
    }
}
