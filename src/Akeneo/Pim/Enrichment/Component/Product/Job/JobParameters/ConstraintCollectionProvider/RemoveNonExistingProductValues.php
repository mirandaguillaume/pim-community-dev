<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class RemoveNonExistingProductValues implements ConstraintCollectionProviderInterface
{
    public function __construct(private array $supportedJobNames)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'attribute_code' => new NotNull(),
                'attribute_options' => [
                    new NotNull(),
                    new Type('array'),
                    new All([
                        new Type('string'),
                    ])
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
