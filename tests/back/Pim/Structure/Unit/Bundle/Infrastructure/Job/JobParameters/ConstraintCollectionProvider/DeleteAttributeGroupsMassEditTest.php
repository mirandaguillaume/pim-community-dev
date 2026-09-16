<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Infrastructure\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\JobParameters\ConstraintCollectionProvider\DeleteAttributeGroupsMassEdit;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAttributeGroupsMassEditTest extends TestCase
{
    private DeleteAttributeGroupsMassEdit $sut;

    protected function setUp(): void
    {
        $this->sut = new DeleteAttributeGroupsMassEdit(['delete_attribute_groups']);
    }

    public function test_it_is_a_constraint_collection_provider(): void
    {
        $this->assertInstanceOf(ConstraintCollectionProviderInterface::class, $this->sut);
    }

    public function test_it_supports_a_job_with_a_supported_name(): void
    {
        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn('delete_attribute_groups');

        $this->assertTrue($this->sut->supports($job));
    }

    public function test_it_does_not_support_a_job_with_an_unsupported_name(): void
    {
        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn('some_other_job');

        $this->assertFalse($this->sut->supports($job));
    }

    public function test_it_requires_the_expected_fields(): void
    {
        $fields = $this->sut->getConstraintCollection()->fields;

        $this->assertArrayHasKey('replacement_attribute_group_code', $fields);
        $replacementCode = $this->onlyConstraintOf($fields['replacement_attribute_group_code']);
        $this->assertInstanceOf(NotBlank::class, $replacementCode);
        $this->assertTrue($replacementCode->allowNull);
        $this->assertArrayHasKey('filters', $fields);
        $this->assertInstanceOf(NotNull::class, $this->onlyConstraintOf($fields['filters']));
        $this->assertArrayHasKey('actions', $fields);
        $this->assertArrayHasKey('users_to_notify', $fields);
        $this->assertArrayHasKey('is_user_authenticated', $fields);
    }

    private function onlyConstraintOf(Required $required): object
    {
        return $required->constraints[0];
    }
}
