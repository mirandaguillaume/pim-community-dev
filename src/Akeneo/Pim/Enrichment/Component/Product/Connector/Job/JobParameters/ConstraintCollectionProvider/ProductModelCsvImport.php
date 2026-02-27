<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCsvImport implements ConstraintCollectionProviderInterface
{
    public function __construct(private readonly ConstraintCollectionProviderInterface $simpleProvider, private readonly array $supportedJobNames) {}

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['decimalSeparator'] = new NotBlank();
        $constraintFields['dateFormat'] = new NotBlank();
        $constraintFields['enabled'] = new Type('bool');
        $constraintFields['categoriesColumn'] = new NotBlank();
        $constraintFields['familyVariantColumn'] = new NotBlank();
        $constraintFields['enabledComparison'] = new Type('bool');
        $constraintFields['realTimeVersioning'] = new Type('bool');
        $constraintFields['invalid_items_file_format'] = new NotBlank();

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
