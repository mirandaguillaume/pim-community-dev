<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\ProductValue;

use Twig\Environment;

/**
 * Allows to configure a related template for value rendering
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TwigProperty extends FieldProperty
{
    /** @staticvar string */
    final public const string TEMPLATE_KEY = 'template';

    public function __construct(protected Environment $environment)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function convertValue($value)
    {
        if ($value) {
            return $this->getTemplate()->render(['value' => $value]);
        }

        return null;
    }

    /**
     * Load twig template
     */
    protected function getTemplate(): \Twig\Template
    {
        $templateName = $this->get(self::TEMPLATE_KEY);

        return $this->environment->loadTemplate(
            $this->environment->getTemplateClass($templateName),
            $templateName,
        );
    }
}
