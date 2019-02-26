<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param $eventId integer
     * @return Event[]
     */
    public function getEventOperations($eventId) : array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.userEvents', 'ue')
            ->innerJoin('ue.user', 'u')
            ->leftJoin('e.operations', 'o')
            ->andWhere('e.id = :eventId')
            ->andWhere('ue.administrator = true')
            ->addSelect('ue')
            ->addSelect('u')
            ->addSelect('o')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $eventId
     * @return array
     */
    public function getEventUsers($eventId) : array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.userEvents', 'ue')
            ->innerJoin('ue.user', 'u')
            ->andWhere('e.id = :eventId')
            ->addSelect('u')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getResult();
    }
}
