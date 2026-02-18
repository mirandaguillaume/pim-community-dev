<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Renders a localized datetime value (similarly to
 * Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\DateTimeProperty), but apply the current user's timezone
 * on it.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateTimeWithUserTimezoneProperty extends FieldProperty
{
    public function __construct(
        TranslatorInterface $translator,
        private readonly PresenterInterface $presenter,
        private readonly UserContext $userContext
    ) {
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        return $this->presenter->present(
            $value,
            [
                'locale'   => $this->userContext->getUiLocaleCode(),
                'timezone' => $this->userContext->getUserTimezone(),
            ]
        );
    }
}
