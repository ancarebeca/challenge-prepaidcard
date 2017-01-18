<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Card;

interface CardRepositoryInterface
{
    /**
     * @param string $id
     * @return Card | null
     */
    public function findOneById(string $id);

    /**
     * @param Card $card
     */
    public function save(Card $card);
}