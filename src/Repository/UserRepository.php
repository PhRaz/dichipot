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
     * @param $userId integer
     * @return array
     */
    public function getUserEvents($userId) : array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.UserEvents', 'ue')
            ->innerJoin('ue.event', 'e')
            ->andWhere('u.id = :userId')
            ->addSelect('ue')
            ->addSelect('e')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }
}
