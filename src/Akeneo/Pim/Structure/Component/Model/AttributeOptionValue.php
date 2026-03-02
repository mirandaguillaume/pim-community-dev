<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_attribute_option_value')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'searchunique_idx', columns: ['locale_code', 'option_id'])]
class AttributeOptionValue implements AttributeOptionValueInterface
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /**
     * @var AttributeOptionInterface
     */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface::class, inversedBy: 'optionValues')]
    #[ORM\JoinColumn(name: 'option_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $option;

    /**
     * LocaleInterface scope
     *
     * @var string
     */
    #[ORM\Column(name: 'locale_code', type: Types::STRING, length: 20, nullable: true)]
    protected $locale;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $value;

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
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(AttributeOptionInterface $option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->value = (string) $label;

        return $this;
    }
}
