<?php

namespace App\Repository;

use App\Entity\IiifServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method IiifServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method IiifServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method IiifServer[]    findAll()
 * @method IiifServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IiifServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IiifServer::class);
    }

    // /**
    //  * @return IiifServer[] Returns an array of IiifServer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IiifServer
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
