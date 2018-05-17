<?php

namespace App\Repository\Project;

use App\Entity\Project\RegisteredUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RegisteredUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegisteredUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegisteredUser[]    findAll()
 * @method RegisteredUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegisteredUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RegisteredUser::class);
    }

//    /**
//     * @return RegisteredUser[] Returns an array of RegisteredUser objects
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
    public function findOneBySomeField($value): ?RegisteredUser
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
