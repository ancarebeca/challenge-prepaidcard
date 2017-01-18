<?php

namespace AppBundle\Repository\Doctrine;

use AppBundle\Entity\Transaction;
use AppBundle\Repository\TransactionRepositoryInterface;
use Doctrine\ORM\EntityRepository;


class TransactionRepository extends EntityRepository implements TransactionRepositoryInterface
{
    /**
     * @param string $id
     * @return Transaction | null
     */
    public function findOneById(string $id)
    {
        return $this->find($id);
    }

    /**
     * @param Transaction $transaction
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function save(Transaction $transaction)
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();
    }
}