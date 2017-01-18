<?php

namespace AppBundle\Entity;

use AppBundle\Exception\DomainException;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Money\Money;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Doctrine\CardRepository")
 * @ORM\Table(name="card")
 * @ExclusionPolicy("None")
 */
class Card
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $balance;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $blockedAmount;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="card", cascade={"persist"})
     */
    private $transactions;

    public function __construct()
    {
        $this->setId(Uuid::uuid4()->toString());
        $this->setBalance(Money::GBP('0'));
        $this->setBlockedAmount(Money::GBP('0'));
        $this->setTransactions(new ArrayCollection());
    }

    /**
     * @return Money
     */
    public function getBalance(): Money
    {
        return $this->balance;
    }

    /**
     * @return Money
     */
    public function getAvailableBalance(): Money
    {
        return $this->balance->subtract($this->blockedAmount);
    }

    /**
     * @return ArrayCollection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param Money $amount
     */
    public function topUp(Money $amount)
    {
        $this->balance = $this->balance->add($amount);
        $this->transactions->add(new Transaction($amount, 'Top-up', $this));
    }

    /**
     * @param Money $amount
     * @param string $description
     * @return Transaction
     * @throws Exception
     */
    public function getAuthorizationRequest(Money $amount, string $description): Transaction
    {
        if ($this->getAvailableBalance()->lessThan($amount)) {
            throw new DomainException('You current balance is not enough to complete this transaction');
        }

        $transaction = new Transaction($amount, $description, $this);
        $this->transactions->add($transaction);
        $this->setBlockedAmount($this->getBlockedAmount()->add($amount));

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     * @param Money $amount
     * @throws DomainException
     */
    public function capture(Transaction $transaction, Money $amount)
    {
        if ($this->blockedAmount->lessThan($amount)) {
            throw new DomainException('You cannot capture more than the transaction value was authorized');
        }

        $transaction->capture($amount);

        $this->setBalance($this->getBalance()->subtract($amount));
        $this->setBlockedAmount($this->getBlockedAmount()->subtract($amount));
    }

    /**
     * @param Transaction $transaction
     * @param Money $amount
     * @throws DomainException
     */
    public function reverse(Transaction $transaction, Money $amount)
    {
        if ($this->blockedAmount->lessThan($amount)) {
            throw new DomainException('You cannot reverse more than the transaction value was authorized');
        }

        $transaction->reverse($amount);

        $this->setBlockedAmount($this->getBlockedAmount()->subtract($amount));
    }

    /**
     * @param Transaction $transaction
     */
    public function refund(Transaction $transaction)
    {
        $transaction->refund();

        $this->setBalance($this->getBalance()->add($transaction->getAmount()));
    }

    /**
     * @return Money
     */
    public function getBlockedAmount(): Money
    {
        return $this->blockedAmount;
    }

    /**
     * @param Money $amount
     */
    private function setBalance(Money $amount)
    {
        $this->balance = $amount;
    }

    /**
     * @param Money $amount
     */
    private function setBlockedAmount(Money $amount)
    {
        $this->blockedAmount = $amount;
    }

    /**
     * @param string $id
     */
    private function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @param ArrayCollection $transactions
     */
    private function setTransactions(ArrayCollection $transactions)
    {
        $this->transactions = $transactions;
    }

}