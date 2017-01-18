<?php

namespace AppBundle\Entity;

use AppBundle\Exception\DomainException;
use JMS\Serializer\Annotation as JMS;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Doctrine\TransactionRepository")
 * @ORM\Table(name="transaction")
 * @JMS\ExclusionPolicy("none")
 */
class Transaction
{
    /**
     * @var string
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var Card
     *
     * @ORM\ManyToOne(targetEntity="Card", inversedBy="transaction")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     * @JMS\Exclude
     */
    private $card;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $capturedAmount;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $reversedAmount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime",  nullable=true)
     */
    private $refundedAt;

    /**
     * @param Money $amount
     * @param string $description
     * @param Card $card
     */
    public function __construct(Money $amount, string $description, Card $card)
    {
        $this->amount = $amount;
        $this->id = Uuid::uuid4()->toString();
        $this->description = $description;
        $this->card = $card;
        $this->capturedAmount = Money::GBP('0');
        $this->reversedAmount = Money::GBP('0');
        $this->refundedAt = null;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @return String
     */
    public function getDescription(): String
    {
        return $this->description;
    }

    /**
     * @return Card
     */
    public function getCard(): Card
    {
        return $this->card;
    }

    /**
     * @param Money $amount
     * @throws \Exception
     */
    public function capture(Money $amount)
    {
        if ($this->getRemainAmount()->lessThan($amount)) {
             throw new DomainException('You cannot capture more than the transaction amount');
        }

        $this->setCapturedAmount($this->getCapturedAmount()->add($amount));
    }

    /**
     * @param Money $amount
     * @throws \Exception
     */
    public function reverse(Money $amount)
    {
        if ($this->getRemainAmount()->lessThan($amount)) {
             throw new DomainException('You cannot reverse more than the remain transaction amount');
        }

        $this->setReversedAmount($amount);
    }


    public function refund()
    {
        if (!$this->canBeRefunded()) {
             throw new DomainException('You cannot refund because the transaction amount has not been captured');
        }

        $this->refundedAt = new \DateTime();
    }

    /**
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->refundedAt instanceof \DateTime;
    }

    /**
     * @return Money
     */
    public function getReversedAmount(): Money
    {
        return $this->reversedAmount;
    }

    /**
     * @return Money
     */
    private function getRemainAmount(): Money
    {
        return $this->getAmount()->subtract($this->getCapturedAmount()->add($this->getReversedAmount()));
    }

    /**
     * @return Money
     */
    public function getCapturedAmount(): Money
    {
        return $this->capturedAmount;
    }

    /**
     * @param Money $reversedAmount
     */
    private function setReversedAmount(Money $reversedAmount)
    {
        $this->reversedAmount = $reversedAmount;
    }

    /**
     * @param Money $capturedAmount
     */
    private function setCapturedAmount(Money $capturedAmount)
    {
        $this->capturedAmount = $capturedAmount;
    }

    /**
     * @return bool
     */
    private function canBeRefunded(): bool
    {
        return $this->getCapturedAmount()->compare($this->getAmount()) === 0;
    }

    /**
     * @return string
     * @JMS\VirtualProperty
     * @JMS\SerializedName("card_id")
     */
    public function getCardId(): string
    {
        return $this->card->getId();
    }
}