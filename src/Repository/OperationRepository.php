<?php

namespace App\Repository;

use App\Entity\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function getNbOperation(): int
    {
        return $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $operationId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findForUpdate($operationId)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.expenses', 'oe')
            ->innerJoin('oe.user', 'eu')
            ->innerJoin('eu.userEvents', 'ue')
            ->addSelect('oe')
            ->addSelect('eu')
            ->addSelect('ue')
            ->andWhere('o.id = :operationId')
            ->andWhere('ue.event = o.event')
            ->setParameter('operationId', $operationId)
            ->addOrderBy('ue.pseudo', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
