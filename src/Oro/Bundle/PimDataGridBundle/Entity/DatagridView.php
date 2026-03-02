<?php

namespace Oro\Bundle\PimDataGridBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Datagrid view entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository::class)]
#[ORM\Table(name: 'pim_datagrid_view')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class DatagridView
{
    /** @staticvar string */
    final public const TYPE_PUBLIC = 'public';
    final public const TYPE_PRIVATE = 'private';

    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 100)]
    protected $label;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    protected $type;

    /** @var UserInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\UserManagement\Component\Model\UserInterface::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $owner;

    /** @var string */
    #[ORM\Column(name: 'datagrid_alias', type: Types::STRING)]
    protected $datagridAlias;

    /** @var array */
    #[ORM\Column(type: Types::ARRAY)]
    protected $columns = [];

    /** @var string */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected $filters;

    /**
     * Indicates whether a view can be seen by users who don't own it
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->type === self::TYPE_PUBLIC;
    }

    /**
     * Get id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return DatagridView
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return DatagridView
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set owner
     *
     *
     * @return DatagridView
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return UserInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set datagrid alias
     *
     * @param string $datagridAlias
     *
     * @return DatagridView
     */
    public function setDatagridAlias($datagridAlias)
    {
        $this->datagridAlias = $datagridAlias;

        return $this;
    }

    /**
     * Get datagrid alias
     *
     * @return string
     */
    public function getDatagridAlias()
    {
        return $this->datagridAlias;
    }

    /**
     * Set columns
     *
     *
     * @return DatagridView
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set column order
     *
     * @param string $order
     *
     * @return DatagridView
     */
    public function setOrder($order)
    {
        $this->columns = empty($order) ? [] : explode(',', $order);

        return $this;
    }

    /**
     * Get column order
     *
     * @return string
     */
    public function getOrder()
    {
        return implode(',', $this->columns);
    }

    /**
     * Set filters
     *
     * @param string $filters
     *
     * @return DatagridView
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get filters
     *
     * @return string
     */
    public function getFilters()
    {
        return $this->filters;
    }
}
