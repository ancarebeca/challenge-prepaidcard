<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Card;
use AppBundle\Entity\Transaction;
use Money\Money;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @group Unit
 */
class CardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Card
     */
    private $card;

    /**
     * @var Transaction | ProphecyInterface
     */
    private $transaction;

    protected function setUp()
    {
        $this->transaction = $this->prophesize(Transaction::class);
        $this->card = new Card();
    }

    /**
     * @test
     */
    public function create()
    {
        $this->assertNotEmpty($this->card->getId());
        $this->assertEquals('0', $this->card->getBalance()->getAmount());
        $this->assertEquals('0', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('0', $this->card->getBlockedAmount()->getAmount());
        $this->assertCount(0, $this->card->getTransactions());
    }

    /**
     * @test
     */
    public function canTopUp()
    {
        $this->card->topUp(Money::GBP('200'));
        $this->assertEquals('200', $this->card->getBalance()->getAmount());
        $this->assertEquals('200', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('0', $this->card->getBlockedAmount()->getAmount());
    }

    /**
     * @test
     */
    public function canMakeAnAuthorizationRequest()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('200'));

        $coffeeTransaction = $this->card->getAuthorizationRequest(Money::GBP('100'), $merchantName);
        $this->assertEquals('100', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('100', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('200', $this->card->getBalance()->getAmount());
        $this->assertInstanceOf(Transaction::class, $coffeeTransaction);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You current balance is not enough to complete this transaction
     */
    public function cannotMakeAnAuthorizationRequest()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('200'));

        $this->card->getAuthorizationRequest(Money::GBP('500'), $merchantName);
    }

    /**
     * @test
     */
    public function canCapture()
    {
        $merchantName = 'CoffeeLtd';
        $captureAmount = Money::GBP('100');

        $this->transaction->capture($captureAmount)->shouldBeCalled();

        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('100'), $merchantName);

        $this->card->capture($this->transaction->reveal(), $captureAmount);
        $this->assertEquals('400', $this->card->getBalance()->getAmount());
        $this->assertEquals('400', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('000', $this->card->getBlockedAmount()->getAmount());
    }

    /**
     * @test
     */
    public function canCapturePartialAmount()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('400'), $merchantName);
        $this->card->capture($this->transaction->reveal(), Money::GBP('100'));
        $this->card->capture($this->transaction->reveal(), Money::GBP('100'));
        $this->card->capture($this->transaction->reveal(), Money::GBP('100'));

        $this->assertEquals('100', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('200', $this->card->getBalance()->getAmount());
        $this->assertEquals('100', $this->card->getAvailableBalance()->getAmount());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot capture more than the transaction value was authorized
     */
    public function cannotCaptureMoreThanTheTransactionValueWasAuthorized()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('400'), $merchantName);
        $this->card->capture($this->transaction->reveal(), Money::GBP('600'));
    }

    /**
     * @test
     */
    public function canReverse()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('400'), $merchantName);
        $this->assertEquals('100', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('400', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('500', $this->card->getBalance()->getAmount());

        $this->card->reverse($this->transaction->reveal(), Money::GBP('100'));
        $this->assertEquals('200', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('300', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('500', $this->card->getBalance()->getAmount());
    }

    /**
     * @test
     */
    public function canCaptureAndReverseTheRemainedAmount()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('400'), $merchantName);
        $this->assertEquals('100', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('400', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('500', $this->card->getBalance()->getAmount());

        $this->card->capture($this->transaction->reveal(), Money::GBP('100'));
        $this->assertEquals('100', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('300', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('400', $this->card->getBalance()->getAmount());

        $this->card->reverse($this->transaction->reveal(), Money::GBP('100'));
        $this->assertEquals('200', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('200', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('400', $this->card->getBalance()->getAmount());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot reverse more than the transaction value was authorized
     */
    public function cannotReverseMoreThanTheTransactionValueWasAuthorized()
    {
        $merchantName = 'CoffeeLtd';
        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('400'), $merchantName);
        $this->card->reverse($this->transaction->reveal(), Money::GBP('600'));
    }

    /**
     * @test
     */
    public function canRefund()
    {
        $merchantName = 'CoffeeLtd';

        $this->transaction->capture(Argument::type(Money::class))->shouldBeCalled();
        $this->transaction->refund()->shouldBeCalled();
        $this->transaction->getAmount()->willReturn(Money::GBP('400'));

        $this->card->topUp(Money::GBP('500'));
        $this->card->getAuthorizationRequest(Money::GBP('400'), $merchantName);
        $this->card->capture($this->transaction->reveal(), Money::GBP('400'));

        $this->card->refund($this->transaction->reveal());
        $this->assertEquals('500', $this->card->getAvailableBalance()->getAmount());
        $this->assertEquals('0', $this->card->getBlockedAmount()->getAmount());
        $this->assertEquals('500', $this->card->getBalance()->getAmount());
    }
}
