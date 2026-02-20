<?php

namespace Oro\Bundle\ConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;

/**
 * ConfigValueRepository
 *
 */
class ConfigValueRepository extends EntityRepository
{
    /**
     * Remove values by params
     *
     * @param integer $configId
     * @param array   $removed
     */
    public function removeValues($configId, $removed)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $this->getEntityManager()->beginTransaction();
        foreach ($removed as $item) {
            $builder->delete(ConfigValue::class, 'cv')
                ->where('cv.config = :configId')
                ->andWhere('cv.name = :name')
                ->andWhere('cv.section = :section')
                ->setParameter('configId', $configId)
                ->setParameter('section', $item[0])
                ->setParameter('name', $item[1]);
            $builder->getQuery()->execute();
        }
        $this->getEntityManager()->commit();
    }

    /**
     * Return a ConfigValue
     *
     * @param string  $section
     * @param string  $entityName
     * @param integer $scopeId
     */
    public function getSectionForEntityAndScope($section, $entityName, $scopeId): ?string
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.config', 'c')
            ->where('c.scopedEntity = :entity')
            ->andWhere('c.recordId = :scope')
            ->andWhere('v.section = :section')
            ->setParameters([
                'entity'  => $entityName,
                'scope'   => $scopeId,
                'section' => $section
            ])->getQuery()->getOneOrNullResult();
    }
}
