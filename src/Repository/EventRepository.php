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
    public function getEventOperations($eventId): ?Event
    {
        $result =  $this->createQueryBuilder('e')
            ->andWhere('e.id = :eventId')

            ->leftJoin('e.operations', 'o')
            ->addSelect('o')

            ->leftJoin('o.user', 'ou')
            ->addSelect('ou')

            ->leftJoin('o.expenses', 'ex')
            ->addSelect('ex')

            ->leftJoin('ex.user', 'exu')
            ->addSelect('exu')

            ->leftJoin('o.payments', 'p')
            ->addSelect('p')

            ->leftJoin('ou.userEvents', 'oue') // operation author pseudo
            ->addSelect('oue')
            ->andWhere('oue.event = e')

            ->leftJoin('exu.userEvents', 'ue') // expense author pseudo
            ->addSelect('ue')
            ->andWhere('ue.event = e')

            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
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
