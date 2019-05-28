<?php

namespace App\Repository;

use App\Entity\UserEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserEvent[]    findAll()
 * @method UserEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserEvent::class);
    }

    /**
     * return he number of event created as admin
     * @param $user
     * @return int number of event
     * @throws \Exception
     */
    public function getUserNbEvent(User $user): int
    {
        return $this->createQueryBuilder('ue')
            ->select('count(ue.id)')
            ->join('ue.user', 'ueu')
            ->andWhere('ueu.id = :userId')
            ->andWhere('ue.administrator = true')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
