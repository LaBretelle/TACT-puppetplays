<?php

namespace App\Repository;

use App\Entity\TeiSchema;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TeiSchema|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeiSchema|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeiSchema[]    findAll()
 * @method TeiSchema[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeiSchemaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TeiSchema::class);
    }

//    /**
//     * @return TeiSchema[] Returns an array of TeiSchema objects
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
    public function findOneBySomeField($value): ?TeiSchema
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
