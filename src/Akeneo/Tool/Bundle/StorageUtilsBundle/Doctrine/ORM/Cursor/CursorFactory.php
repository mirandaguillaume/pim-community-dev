<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class CursorFactory to instantiate cursor to iterate entities
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorFactory implements CursorFactoryInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $entityClass;

    /**
     * @param string        $cursorClass   class name implementation
     * @param int           $pageSize
     */
    public function __construct(
        protected $cursorClass,
        EntityManager $entityManager,
        protected $pageSize
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = []): CursorInterface
    {
        $pageSize = $options['page_size'] ?? $this->pageSize;

        return new $this->cursorClass($queryBuilder, $this->entityManager, $pageSize);
    }
}
