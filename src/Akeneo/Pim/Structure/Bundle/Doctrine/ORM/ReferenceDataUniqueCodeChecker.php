<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM;

use Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\AbstractReferenceDataUniqueCodeChecker;
use Doctrine\ORM\Mapping\ClassMetadata;
use Webmozart\Assert\Assert;

/**
 * Checks if the ReferenceData has a unique code constraint.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataUniqueCodeChecker extends AbstractReferenceDataUniqueCodeChecker
{
    /**
     * {@inheritdoc}
     */
    protected function getCodeFieldMapping($referenceDataClass): \Doctrine\ORM\Mapping\FieldMapping
    {
        $metadata = $this->om->getClassMetadata($referenceDataClass);
        Assert::isInstanceOf($metadata, ClassMetadata::class);

        return $metadata->getFieldMapping('code');
    }
}
