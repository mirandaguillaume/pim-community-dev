<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * The attribute requirement for a channel and a family
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_attribute_requirement')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'searchunique_idx', columns: ['channel_id', 'family_id', 'attribute_id'])]
class AttributeRequirement implements AttributeRequirementInterface
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /**
     * @var Family
     */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\FamilyInterface::class, inversedBy: 'requirements')]
    #[ORM\JoinColumn(name: 'family_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $family;

    /**
     * @var AttributeInterface
     */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeInterface::class)]
    #[ORM\JoinColumn(name: 'attribute_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $attribute;

    /**
     * @var ChannelInterface
     */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface::class)]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $channel;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $required = false;

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(AttributeInterface $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->attribute->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCode()
    {
        return $this->channel->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->required;
    }
}
