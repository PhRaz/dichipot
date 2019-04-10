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
     * @param $full
     * @return Event
     * @throws \Exception
     */
    public function getEventOperations($eventId, $full = false): ?Event
    {
        $query = $this->createQueryBuilder('event')
            ->andWhere('event.id = :eventId')
            ->leftJoin('event.operations', 'operations')
            ->addSelect('operations')
            ->setParameter('eventId', $eventId);

        if ($full) {
            $query
                ->innerJoin('operations.user', 'operations_authors')
                ->addSelect('operations_authors')
                ->innerJoin('operations_authors.userEvents', 'operations_authors_pseudos')// operation author pseudo
                ->addSelect('operations_authors_pseudos')
                ->andWhere('operations_authors_pseudos.event = event')
                ->innerJoin('operations.expenses', 'operations_expenses')
                ->addSelect('operations_expenses')
                ->innerJoin('operations_expenses.user', 'expenses_authors')
                ->addSelect('expenses_authors')
                ->innerJoin('expenses_authors.userEvents', 'expenses_authors_pseudos')// expense author pseudo
                ->addSelect('expenses_authors_pseudos')
                ->andWhere('expenses_authors_pseudos.event = event')
                ->addOrderBy('operations.date', 'ASC')
                ->addOrderBy('expenses_authors_pseudos.pseudo', 'ASC');
        }

        $result = $query
            ->getQuery()
            ->getOneOrNullResult();
        return $result;
    }
}
