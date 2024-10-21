<?php

namespace App\Repository;

use App\Entity\HabitatImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HabitatImage>
 */
class HabitatImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HabitatImage::class);
    }

    //    /**
    //     * @return HabitatImage[] Returns an array of HabitatImage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findRandomImages(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult("path", "path");

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT * FROM animal_image ORDER BY RAND() LIMIT 2',
            $rsm
        );

        return $query->getResult();
    }
}
