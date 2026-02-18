<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Able to render changeset value
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangesetProperty extends FieldProperty
{
    public function __construct(TranslatorInterface $translator, protected PresenterInterface $presenter)
    {
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($changeset)
    {
        foreach ($changeset as $code => $diff) {
            $changeset[$code] = [
                'old' => $this->presenter->present($diff['old'], ['locale' => $this->translator->getLocale()]),
                'new' => $this->presenter->present($diff['new'], ['locale' => $this->translator->getLocale()]),
            ];
        }

        return $changeset;
    }
}
