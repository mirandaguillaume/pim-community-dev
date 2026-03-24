<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

/**
 * DefaultParameters for product quick export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuickExport implements DefaultValuesProviderInterface
{
    protected \Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface $simpleProvider;

    /** @var string[] */
    protected array $supportedJobNames;

    /**
     * @param string[]                       $supportedJobNames
     */
    public function __construct(DefaultValuesProviderInterface $simpleProvider, array $supportedJobNames)
    {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $parameters = $this->simpleProvider->getDefaultValues();
        $parameters['filters'] = null;
        $parameters['selected_properties'] = null;
        $parameters['with_media'] = true;
        $parameters['locale'] = null;
        $parameters['scope'] = null;
        $parameters['ui_locale'] = null;
        $parameters['with_label'] = false;
        $parameters['header_with_label'] = false;
        $parameters['file_locale'] = null;
        $parameters['with_uuid'] = false;

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
