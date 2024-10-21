<?php

namespace App\Repository;

use App\Entity\AnimalImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

/**
 * @extends ServiceEntityRepository<AnimalImage>
 */
class AnimalImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnimalImage::class);
    }

    //    /**
    //     * @return AnimalImage[] Returns an array of AnimalImage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AnimalImage
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findRandomImages(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult("path", "path");

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT * FROM animal_image ORDER BY RAND() LIMIT 3',
            $rsm
        );

        return $query->getResult();
    }
}
