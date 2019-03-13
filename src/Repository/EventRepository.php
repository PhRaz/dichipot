<?php

namespace App\Repository;

use App\Entity\Event;
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
     * @return Event
     * @throws \Exception
     */
    public function getEventOperations($eventId) : Event
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.userEvents', 'ue')
            ->innerJoin('ue.user', 'u')
            ->leftJoin('e.operations', 'o')
            ->leftJoin('o.expenses', 'ex')
            ->leftJoin('ex.user', 'exu')
            ->leftJoin('o.payments', 'p')
            ->leftJoin('p.user', 'pu')
            ->andWhere('e.id = :eventId')
            ->andWhere('ue.administrator = true')
            ->addSelect('ue')
            ->addSelect('u')
            ->addSelect('o')
            ->addSelect('ex')
            ->addSelect('exu')
            ->addSelect('pu')
            ->addSelect('p')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $eventId
     * @return Event
     * @throws \Exception
     */
    public function getEventUsers($eventId) : Event
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.userEvents', 'ue')
            ->innerJoin('ue.user', 'u')
            ->andWhere('e.id = :eventId')
            ->addSelect('ue')
            ->addSelect('u')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
