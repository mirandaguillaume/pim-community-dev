<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\UserIdMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\UserIdMustBeValidValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIdMustBeValidTest extends TestCase
{
    private UserIdMustBeValid $sut;

    protected function setUp(): void
    {
        $this->sut = new UserIdMustBeValid();
    }

    public function test_it_is_a_symfony_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_targets_a_property(): void
    {
        $this->assertSame(Constraint::PROPERTY_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_can_be_mapped_on_the_pim_user_id_property_of_the_consent_command(): void
    {
        $metadata = new ClassMetadata(ConsentAppAuthenticationCommand::class);
        $metadata->addPropertyConstraint('pimUserId', $this->sut);

        $propertyMetadata = $metadata->getPropertyMetadata('pimUserId');

        $this->assertCount(1, $propertyMetadata);
        $this->assertSame([$this->sut], $propertyMetadata[0]->getConstraints());
    }

    public function test_it_is_validated_by_the_validator_matching_its_own_name(): void
    {
        $validator = $this->sut->validatedBy();
        $this->assertSame(UserIdMustBeValidValidator::class, $validator);
        // `Foo::class` is resolved lexically and never autoloads, so the assertion above
        // compares two spellings of the same naming convention: rename or delete the
        // validator and it still passes. Verified by mutation -- the class was renamed
        // outright and the suite stayed green. This is what makes it bite.
        $this->assertTrue(class_exists($validator), "UserIdMustBeValidValidator must actually exist");
    }

    public function test_it_exposes_a_default_message_because_the_mapping_declares_no_option(): void
    {
        $this->assertSame('User ID must be valid', $this->sut->message);
    }

    public function test_it_lets_the_validation_mapping_override_the_message(): void
    {
        $constraint = new UserIdMustBeValid([
            'message' => 'akeneo_connectivity.connection.connect.apps.constraint.user_id.must_be_valid',
        ]);

        $this->assertSame(
            'akeneo_connectivity.connection.connect.apps.constraint.user_id.must_be_valid',
            $constraint->message,
        );
    }
}
