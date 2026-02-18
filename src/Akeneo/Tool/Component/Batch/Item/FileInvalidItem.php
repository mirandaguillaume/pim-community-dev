<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * This class handles invalid items that could be raised by Reader or Processor. This invalid item class will handle
 * file invalid items (for example items coming from a csv file)
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileInvalidItem implements InvalidItemInterface
{
    /** @var array */
    protected $invalidData;

    /**
     * @param int   $itemPosition
     */
    public function __construct(array $invalidData, protected $itemPosition)
    {
        $this->invalidData = $invalidData;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }

    /**
     * @return int
     */
    public function getItemPosition()
    {
        return $this->itemPosition;
    }
}
