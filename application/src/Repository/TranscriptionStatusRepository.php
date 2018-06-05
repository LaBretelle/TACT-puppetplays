<?php

namespace App\Repository;

use App\Entity\TranscriptionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TranscriptionStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranscriptionStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranscriptionStatus[]    findAll()
 * @method TranscriptionStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranscriptionStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TranscriptionStatus::class);
    }

//    /**
//     * @return TranscriptionStatus[] Returns an array of TranscriptionStatus objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TranscriptionStatus
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
