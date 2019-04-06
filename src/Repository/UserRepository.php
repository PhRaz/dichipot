<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourne les évènements pour userId, chaque évènement contient la liste des participants.
     *
     * @param $userId integer
     * @return User
     * @throws \Exception
     */
    public function getUserEvents($userId): User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userEvents', 'ue')
            ->leftJoin('ue.event', 'e')
            ->leftJoin('e.userEvents', 'ue2')
            ->leftJoin('ue2.user', 'u2')
            ->addSelect('ue')
            ->addSelect('e')
            ->addSelect('ue2')
            ->addSelect('u2')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->addOrderBy('e.date', 'DESC')
            ->addOrderBy('ue2.pseudo', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getEventUsers($eventId)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.userEvents', 'ue')
            ->innerJoin('ue.event', 'e')
            ->addSelect('ue')
            ->addSelect('e')
            ->andWhere('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->addOrderBy('ue.pseudo')
            ->getQuery()
            ->getResult();
    }
}
