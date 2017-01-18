<?php

namespace AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Transaction;
use AppBundle\Exception\ResourceNotFoundException;
use AppBundle\Repository\CardRepositoryInterface;
use AppBundle\Repository\TransactionRepositoryInterface;
use Money\Money;

class PrepaidCardService
{
    /**
     * @var CardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @param CardRepositoryInterface $cardRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(CardRepositoryInterface $cardRepository, TransactionRepositoryInterface $transactionRepository)
    {
        $this->cardRepository = $cardRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @return Card
     */
    public function createCard(): Card
    {
        $card = new Card();

        $this->cardRepository->save($card);

        return $card;
    }

    /**
     * @param string $cardId
     * @param Money $amount
     *
     * @return Card
     * @throws \Exception
     */
    public function topUp(string $cardId, Money $amount): Card
    {
        /** @var Card $card */
        $card = $this->cardRepository->findOneById($cardId);

        if (!$card instanceof Card) {
            throw new ResourceNotFoundException(sprintf('Card [%s] not found', $cardId));
        }

        $card->topUp($amount);
        $this->cardRepository->save($card);

        return $card;
    }

    /**
     * @param string $cardId
     *
     * @return Card
     * @throws \Exception
     */
    public function getCard(string $cardId): Card
    {
        /** @var Card $card */
        $card = $this->cardRepository->findOneById($cardId);

        if (!$card instanceof Card) {
            throw new ResourceNotFoundException(sprintf('Card [%s] not found', $cardId));
        }

        return $card;
    }

    /**
     * @param string $cardId
     * @param Money $amount
     * @param string $merchantName
     *
     * @return Transaction
     *
     * @throws \Exception
     */
    public function requestAuthorization(string $cardId, Money $amount, string $merchantName) : Transaction
    {
        /** @var Card $card */
        $card = $this->cardRepository->findOneById($cardId);

        if (!$card instanceof Card) {
            throw new ResourceNotFoundException(sprintf('Card [%s] not found', $cardId));
        }

        $transaction = $card->getAuthorizationRequest($amount, $merchantName);
        $this->cardRepository->save($card);

        return $transaction;
    }

    /**
     * @param string $transactionId
     * @param Money $amount
     *
     * @return Transaction|null
     *
     * @throws \Exception
     */
    public function capture(string $transactionId, Money $amount)
    {
        $transaction = $this->transactionRepository->findOneById($transactionId);

        if (!$transaction instanceof Transaction) {
            throw new ResourceNotFoundException(sprintf('Transaction [%s] not found', $transactionId));
        }

        $card = $transaction->getCard();
        $card->capture($transaction, $amount);

        $this->cardRepository->save($card);

        // Send money to the merchant

        return $transaction;
    }

    /**
     * @param string $transactionId
     *
     * @param Money $amount
     *
     * @return Transaction
     * @throws \Exception
     */
    public function reverse(string $transactionId, Money $amount): Transaction
    {
        $transaction = $this->transactionRepository->findOneById($transactionId);

        if (!$transaction instanceof Transaction) {
            throw new ResourceNotFoundException(sprintf('Transaction [%s] not found', $transactionId));
        }

        $card = $transaction->getCard();
        $card->reverse($transaction, $amount);

        $this->cardRepository->save($card);

        return $transaction;
    }

    /**
     * @param string $transactionId
     * @return Transaction
     * @throws \Exception
     */
    public function refund(string $transactionId): Transaction
    {
        $transaction = $this->transactionRepository->findOneById($transactionId);

        if (!$transaction instanceof Transaction) {
            throw new ResourceNotFoundException(sprintf('Transaction [%s] not found', $transactionId));
        }

        $card = $transaction->getCard();
        $card->refund($transaction);

        $this->cardRepository->save($card);

        return $transaction;
    }
}