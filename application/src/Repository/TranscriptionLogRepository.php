<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TranscriptionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranscriptionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranscriptionLog[]    findAll()
 * @method TranscriptionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranscriptionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranscriptionLog::class);
    }

    public function getLastLockLog(Transcription $transcription)
    {
        return $this->createQueryBuilder('tl')
            ->andWhere('tl.transcription = :t')
            ->andWhere('tl.name = :name')
            ->setParameter('t', $transcription)
            ->setParameter('name', AppEnums::TRANSCRIPTION_LOG_LOCKED)
            ->orderBy('tl.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLogs(Transcription $transcription)
    {
        return $this->createQueryBuilder('tl')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name NOT IN(:exceptions)')
          ->setParameter('t', $transcription)
          ->setParameter('exceptions', 'transcription_log_locked')
          ->orderBy('tl.createdAt', 'DESC')
          ->getQuery()
          ->getResult();
    }

    public function userHasTranscription(Transcription $transcription, User $user)
    {
        $results = $this->createQueryBuilder('tl')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name IN(:names)')
          ->setParameter('t', $transcription)
          ->setParameter('user', $user)
          ->setParameter('names', ['transcription_log_locked', 'transcription_log_created', 'transcription_log_waiting_for_validation',' transcription_log_rereaded'])
          ->orderBy('tl.createdAt', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult();

        return count($results) > 0;
    }

    public function userHasValidation(Transcription $transcription, User $user)
    {
        $results = $this->createQueryBuilder('tl')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.transcription = :t')
          ->andWhere('tl.name IN(:names)')
          ->setParameter('t', $transcription)
          ->setParameter('user', $user)
          ->setParameter('names', 'transcription_log_validated')
          ->orderBy('tl.createdAt', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult();

        return count($results) > 0;
    }


    public function getWaitingForOldValidation(Project $project)
    {
        return $this->createQueryBuilder('tl')
          ->leftJoin('tl.transcription', 't')
          ->leftJoin('t.media', 'm')
          ->andWhere('m.project = :project')
          ->andWhere('tl.name = :name')
          ->setParameter('project', $project)
          ->setParameter('name', AppEnums::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION)
          ->groupBy('t.id')
          ->getQuery()
          ->getResult();
    }

    public function getProjectLogs(Project $project)
    {
        return $this->createQueryBuilder('tl')
          ->leftJoin('tl.transcription', 't')
          ->leftJoin('t.media', 'm')
          ->andWhere('m.project = :project')
          ->andWhere('tl.name NOT IN(:exceptions)')
          ->setParameter('project', $project)
          ->setParameter('exceptions', ['transcription_log_created', 'transcription_log_locked'])
          ->orderBy('tl.createdAt', 'DESC')
          ->setMaxResults(200)
          ->getQuery()
          ->getResult();
    }

    public function findAlmostAll()
    {
        return $this->createQueryBuilder('tl')
          ->andWhere('tl.name NOT IN(:exceptions)')
          ->setParameter('exceptions', ['transcription_log_locked', 'transcription_log_created'])
          ->orderBy('tl.createdAt', 'DESC')
          ->setMaxResults(300)
          ->getQuery()
          ->getResult();
    }

    public function findAlmostAllByUser(User $user)
    {
        return $this->createQueryBuilder('tl')
          // le leftJoin et andwhere null permet de ne r??cup que le dernier pour une m??me transcription
          ->leftJoin('App:TranscriptionLog', 'tl2', 'WITH', 'tl2.transcription = tl.transcription AND tl2.user = tl.user AND tl.createdAt < tl2.createdAt')
          ->andWhere('tl2.id IS NULL')
          ->andWhere('tl.user = :user')
          ->andWhere('tl.name NOT IN(:exceptions)')
          ->setParameter('exceptions', ['transcription_log_locked', 'transcription_log_created'])
          ->setParameter('user', $user->getId())
          ->orderBy('tl.createdAt', 'DESC')
          ->groupBy('tl.transcription')
          ->setMaxResults(300)
          ->getQuery()
          ->getResult();
    }
}
