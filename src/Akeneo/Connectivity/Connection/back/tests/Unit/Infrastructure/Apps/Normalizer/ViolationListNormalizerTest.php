<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Normalizer;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViolationListNormalizerTest extends TestCase
{
    private ViolationListNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ViolationListNormalizer();
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_normalizes_a_violation_into_its_untranslated_message_template_and_property_path(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation(
                'The value "unknown_client_id" is not a valid client id.',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid',
                ['{{ value }}' => 'unknown_client_id'],
                null,
                'clientId',
                'unknown_client_id',
            ),
        ]);

        $this->assertSame(
            [
                [
                    'message' => 'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid',
                    'property_path' => 'clientId',
                ],
            ],
            $this->sut->normalize($list),
        );
    }

    public function test_it_normalizes_every_violation_of_the_list_in_order(): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation(
                'interpolated client id message',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
                [],
                null,
                'clientId',
                null,
            ),
            new ConstraintViolation(
                'interpolated user id message',
                'akeneo_connectivity.connection.connect.apps.constraint.user_id.must_be_valid',
                [],
                null,
                'pimUserId',
                null,
            ),
        ]);

        $this->assertSame(
            [
                [
                    'message' => 'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
                    'property_path' => 'clientId',
                ],
                [
                    'message' => 'akeneo_connectivity.connection.connect.apps.constraint.user_id.must_be_valid',
                    'property_path' => 'pimUserId',
                ],
            ],
            $this->sut->normalize($list),
        );
    }

    public function test_it_normalizes_an_empty_violation_list_into_an_empty_array(): void
    {
        $this->assertSame([], $this->sut->normalize(new ConstraintViolationList()));
    }

    public function test_it_throws_when_normalizing_an_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->sut->normalize([['message' => 'a message', 'property_path' => 'clientId']]);
    }

    public function test_it_throws_when_normalizing_an_object_that_is_not_a_violation_list(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->sut->normalize(new ConstraintViolation(
            'interpolated message',
            'a.message.template',
            [],
            null,
            'clientId',
            null,
        ));
    }

    public function test_it_supports_the_normalization_of_a_violation_list(): void
    {
        $this->assertTrue($this->sut->supportsNormalization(new ConstraintViolationList()));
    }

    public function test_it_does_not_support_the_normalization_of_a_single_violation(): void
    {
        $this->assertFalse($this->sut->supportsNormalization(new ConstraintViolation(
            'interpolated message',
            'a.message.template',
            [],
            null,
            'clientId',
            null,
        )));
    }

    public function test_it_does_not_support_the_normalization_of_an_array(): void
    {
        $this->assertFalse($this->sut->supportsNormalization([]));
    }

    public function test_it_does_not_support_the_normalization_of_a_scalar(): void
    {
        $this->assertFalse($this->sut->supportsNormalization('a string'));
    }
}
