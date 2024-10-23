<?php

namespace App\Repository;


use App\Entity\AnimalRapport;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnimalRapport>
 */
class AnimalRapportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnimalRapport::class);
    }

    //    /**
    //     * @return AnimalRapport[] Returns an array of AnimalRapport objects
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

    //    public function findOneBySomeField($value): ?AnimalRapport
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByAnimal(int $animal): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.animal = :val')
            ->setParameter('val', $animal)
            ->getQuery()
            ->getResult();
    }

    public function findByDate(DateTime $date): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.date = :val')
            ->setParameter('val', $date)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
