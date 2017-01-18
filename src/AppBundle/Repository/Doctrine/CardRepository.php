<?php

namespace AppBundle\Repository\Doctrine;

use AppBundle\Entity\Card;
use AppBundle\Repository\CardRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class CardRepository extends EntityRepository implements CardRepositoryInterface
{
    /**
     * @param string $id
     * @return Card | null
     */
    public function findOneById(string $id)
    {
        return $this->find($id);
    }

    /**
     * @param Card $card
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function save(Card $card)
    {
        $this->getEntityManager()->persist($card);
        $this->getEntityManager()->flush();
    }
}