<?php

namespace App\Repository;

use App\Entity\EditorialContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EditorialContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method EditorialContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method EditorialContent[]    findAll()
 * @method EditorialContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EditorialContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EditorialContent::class);
    }

    // /**
    //  * @return EditorialContent[] Returns an array of EditorialContent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EditorialContent
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
