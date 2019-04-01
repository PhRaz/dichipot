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
        $result =  $this->createQueryBuilder('event')
            ->andWhere('event.id = :eventId')

            ->leftJoin('event.operations', 'operations')
            ->addSelect('operations')

//            ->leftJoin('operations.user', 'operations_authors')
//            ->addSelect('operations_authors')

//            ->leftJoin('operations_authors.userEvents', 'operations_authors_pseudos') // operation author pseudo
//            ->addSelect('operations_authors_pseudos')
//            ->andWhere('operations_authors_pseudos.event = event')

//            ->leftJoin('operations.expenses', 'operations_expenses')
//            ->addSelect('operations_expenses')

//            ->leftJoin('operations_expenses.user', 'expenses_authors')
//            ->addSelect('expenses_authors')

//            ->leftJoin('expenses_authors.userEvents', 'expenses_authors_pseudos') // expense author pseudo
//            ->addSelect('expenses_authors_pseudos')
//            ->andWhere('expenses_authors_pseudos.event = event')

//            ->leftJoin('operations.payments', 'operations_payments')
//            ->addSelect('operations_payments')

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
