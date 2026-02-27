<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * This class handle invalid items that could be raised by Reader or Processor. This invalid item class will handle
 * object invalid items (for example items coming from DB)
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataInvalidItem implements InvalidItemInterface
{
    public function __construct(protected mixed $invalidData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }
}
