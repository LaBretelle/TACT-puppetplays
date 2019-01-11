<?php

namespace App\Repository;

use App\Entity\ReviewRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReviewRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReviewRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReviewRequest[]    findAll()
 * @method ReviewRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReviewRequest::class);
    }

//    /**
//     * @return ReviewRequest[] Returns an array of ReviewRequest objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReviewRequest
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
