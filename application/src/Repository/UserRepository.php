<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\User;
use App\Service\AppEnums;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByQueryString($string)
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->select('u')
            ->andWhere('u.firstname LIKE :val')
            ->orWhere('u.lastname LIKE :val')
            ->orWhere('u.username LIKE :val')
            ->orWhere('u.email LIKE :val')
            ->setParameter('val', '%' . $string . '%')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getEveryoneByProject(Project $project)
    {
        return $this->createQueryBuilder('u')
          ->leftjoin('u.projectStatus', 'ups')
          ->leftjoin('ups.status', 's')
          ->andWhere('ups.project = :p AND ups.enabled = 1')
          ->setParameter('p', $project)
          ->getQuery()
          ->getResult();
    }

    public function getManagersByProject(Project $project)
    {
        return $this->createQueryBuilder('u')
          ->leftjoin('u.projectStatus', 'ups')
          ->leftjoin('ups.status', 's')
          ->andWhere('ups.project = :p AND s.name = :name AND ups.enabled = 1')
          ->setParameter('p', $project)
          ->setParameter('name', AppEnums::USER_STATUS_MANAGER_NAME)
          ->getQuery()
          ->getResult();
    }

    public function getSubscribedReviewersByProject(Project $project)
    {
        return $this->createQueryBuilder('u')
          ->leftjoin('u.projectStatus', 'ups')
          ->leftjoin('ups.status', 's')
          ->andWhere('ups.project = :p AND s.name IN (:names) AND ups.enabled = 1 AND ups.subscribe = 1')
          ->setParameter('p', $project)
          ->setParameter('names', [AppEnums::USER_STATUS_MANAGER_NAME, AppEnums::USER_STATUS_VALIDATOR_NAME])
          ->getQuery()
          ->getResult();
    }

    public function getManagersOrAdminsByProject(Project $project)
    {
        return $this->createQueryBuilder('u')
          ->leftjoin('u.projectStatus', 'ups')
          ->leftjoin('ups.status', 's')
          ->andWhere(
              '(ups.project = :p AND s.name = :name AND ups.enabled = 1)
              OR (u.roles LIKE :role)'
          )
          ->setParameter('p', $project)
          ->setParameter('name', AppEnums::USER_STATUS_MANAGER_NAME)
          ->setParameter('role', '%ROLE_ADMIN%')
          ->getQuery()
          ->getResult();
    }


    public function getByTranscription(Transcription $transcription)
    {
        $names = ['transcription_log_updated', 'transcription_log_waiting_for_validation', 'transcription_log_rereaded'];

        return $this->createQueryBuilder('u')
          ->leftjoin('u.transcriptionLogs', 'tl')
          ->leftjoin('tl.transcription', 't')
          ->andWhere('t = :transcription')
          ->andWhere('tl.name IN (:names)')
          ->setParameter('transcription', $transcription)
          ->setParameter('names', $names)
          ->getQuery()
          ->getResult();
    }

    public function getByProject(Project $project)
    {
        $names = ['transcription_log_updated', 'transcription_log_waiting_for_validation', 'transcription_log_rereaded'];

        return $this->createQueryBuilder('u')
          ->leftjoin('u.transcriptionLogs', 'tl')
          ->leftjoin('tl.transcription', 't')
          ->leftjoin('t.media', 'm')
          ->leftjoin('m.project', 'p')
          ->andWhere('p = :project')
          ->andWhere('tl.name IN (:names)')
          ->setParameter('project', $project)
          ->setParameter('names', $names)
          ->getQuery()
          ->getResult();
    }

    public function getNonAnonymisedYet()
    {
        $now = new \DateTime;
        $nowMinus3years =  new \DateTime;
        $nowMinus3years->modify('-3 years');

        return $this->createQueryBuilder('u')
          ->andWhere('u.firstname <> :anonymous')
          ->andWhere('u.lastAccess < :nowMinus3years OR u.lastAccess IS NULL')
          ->setParameter('anonymous', 'anonymized-user')
          ->setParameter('nowMinus3years', $nowMinus3years)
          ->getQuery()
          ->getResult();
    }
}
