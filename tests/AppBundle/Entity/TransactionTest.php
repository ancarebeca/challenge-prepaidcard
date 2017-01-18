<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Card;
use AppBundle\Entity\Transaction;
use JMS\Serializer\Tests\Fixtures\Discriminator\Car;
use Money\Money;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @group Unit
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Transaction
     */
    private $transaction;

    protected function setUp()
    {
        $this->transaction = new Transaction(Money::GBP('100.00'), 'Costa coffee', new Card());
    }

    /**
     * @test
     */
    public function create()
    {
        $this->assertNotEmpty($this->transaction->getId());
        $this->assertEquals('100.00', $this->transaction->getAmount()->getAmount());
        $this->assertEquals('Costa coffee', $this->transaction->getDescription());
    }

    /**
     * @test
     */
    public function canCapture()
    {
        $capturedAmount = Money::GBP('20');

        $this->transaction->capture($capturedAmount);
        $this->transaction->capture($capturedAmount);

        $this->assertEquals('100', $this->transaction->getAmount()->getAmount());
        $this->assertEquals('40', $this->transaction->getCapturedAmount()->getAmount());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot capture more than the transaction amount
     */
    public function cannotCaptureMoreThanTheInitialAmountAuthorized()
    {
        $this->transaction->capture(Money::GBP('20'));
        $this->transaction->capture(Money::GBP('200'));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot reverse more than the remain transaction amount
     */
    public function cannotReverseMoreThanTheRemainTransactionAmount()
    {
        $this->transaction->capture(Money::GBP('50'));
        $this->transaction->reverse(Money::GBP('60'));
    }

    /**
     * @test
     */
    public function canReverse()
    {
        $reversedAmount = Money::GBP('50');

        $this->transaction->reverse($reversedAmount);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot capture more than the transaction amount
     */
    public function cannotCaptureAnAmountAlreadyReverse()
    {
        $this->transaction->reverse(Money::GBP('90'));
        $this->transaction->capture(Money::GBP('20'));
    }

    /**
     * @test
     */
    public function canRefund()
    {
        $this->transaction->capture(Money::GBP('100'));

        $this->transaction->refund();
        $this->assertTrue($this->transaction->isRefunded());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot refund because the transaction amount has not been captured
     */
    public function cannotRefundIfTheWholeAmountHasNotBeenCaptured()
    {
        $this->transaction->refund();
    }
}
