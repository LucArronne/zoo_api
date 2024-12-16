<?php

namespace App\Repository;


use App\Entity\AnimalRapport;
use DateTime;
use DateTimeInterface;
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

    public function findByCriteria(?int $animalId = null, ?DateTimeInterface $date = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($animalId !== null) {
            $qb->andWhere('a.animal = :animal')
                ->setParameter('animal', $animalId);
        }

        if ($date !== null) {
            $qb->andWhere('a.date = :date')
                ->setParameter('date', $date)
                ->orderBy('a.id', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
    public function findAnimalLastRapport(int $animalId): ?AnimalRapport
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.animal = :animal')
            ->setParameter('animal', $animalId)
            ->orderBy('a.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
