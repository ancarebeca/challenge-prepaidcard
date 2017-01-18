<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param string $id
     * @return Transaction | null
     */
    public function findOneById(string $id);

    /**
     * @param Transaction $transaction
     */
    public function save(Transaction $transaction);
}