<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
/**
 * Reference data abstract class
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\MappedSuperclass]
abstract class AbstractReferenceData implements ReferenceDataInterface, \Stringable
{
    /** @var mixed */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected $code;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $sortOrder = 1;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): ReferenceDataInterface
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if (null !== $labelProperty = static::getLabelProperty()) {
            $getter = 'get' . ucfirst($labelProperty);
            $label = $this->$getter();

            if (!empty($label)) {
                return (string) $label;
            }
        }

        return sprintf('[%s]', $this->code);
    }
}
