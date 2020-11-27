<?php

namespace App\Repository;

use App\Entity\MetadataMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MetadataMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetadataMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetadataMedia[]    findAll()
 * @method MetadataMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetadataMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MetadataMedia::class);
    }

    // /**
    //  * @return MetadataMedia[] Returns an array of MetadataMedia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MetadataMedia
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
