<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Transaction;
use AppBundle\Repository\CardRepositoryInterface;
use AppBundle\Repository\TransactionRepositoryInterface;
use AppBundle\Service\PrepaidCardService;
use Money\Money;
use Prophecy\Prophecy\ProphecyInterface;
use Symfony\Component\Translation\Catalogue\MergeOperation;

/**
 * @group Unit
 */
class PrepaidCardServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CardRepositoryInterface | ProphecyInterface
     */
    private $cardRepository;

    /**
     * @var TransactionRepositoryInterface | ProphecyInterface
     */
    private $transactionRepository;

    /**
     * @var PrepaidCardService
     */
    private $service;

    protected function setUp()
    {
        $this->cardRepository = $this->prophesize(CardRepositoryInterface::class);
        $this->transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $this->service = new PrepaidCardService($this->cardRepository->reveal(), $this->transactionRepository->reveal());
    }

    /**
     * @test
     */
    public function createCard()
    {
        $this->assertInstanceOf(Card::class, $this->service->createCard());
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Card [card-id-unknown] not found
     */
    public function cannotTopUpIfTheCardIdDoesNotExist()
    {
        $this->cardRepository->findOneById('card-id-unknown')->willReturn(null);

        $this->service->topUp('card-id-unknown', Money::GBP(123));
    }

    /**
     * @test
     */
    public function canTopUp()
    {
        $card = $this->prophesize(Card::class);
        $topUpAmount = Money::GBP('123');

        $this->cardRepository->findOneById('card-id-unknown')->willReturn($card->reveal());
        $this->cardRepository->save($card)->shouldBeCalled();

        $card->topUp($topUpAmount)->shouldBeCalledTimes(1);

        $this->assertEquals($card->reveal(), $this->service->topUp('card-id-unknown', $topUpAmount));
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Card [card-id-unknown] not found
     */
    public function cannotGetACard()
    {
        $this->cardRepository->findOneById('card-id-unknown')->willReturn(null);

        $this->service->getCard('card-id-unknown');
    }

    /**
     * @test
     */
    public function canGetACard()
    {
        $card = new Card();
        $this->cardRepository->findOneById('card-id-unknown')->willReturn($card);

        $this->assertEquals($card, $this->service->getCard('card-id-unknown'));
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Card [card-id-unknown] not found
     */
    public function cannotRequestAuthorizationIfCardDoesNotExist()
    {
        $this->cardRepository->findOneById('card-id-unknown')->willReturn(null);

        $this->service->requestAuthorization('card-id-unknown', Money::GBP('123'), 'name');
    }

    /**
     * @test
     */
    public function canRequestAuthorization()
    {
        $card = new Card();
        $card->topUp(Money::GBP('444'));

        $amount = Money::GBP('123');
        $this->cardRepository->findOneById('card-id-unknown')->willReturn($card);
        $this->cardRepository->save($card)->shouldBeCalled();

        $transaction = $this->service->requestAuthorization('card-id-unknown', $amount, 'name');

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($amount->getAmount(), $transaction->getAmount()->getAmount());
        $this->assertEquals('name', $transaction->getDescription());
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Transaction [uuid] not found
     */
    public function cannotCapture()
    {
        $this->transactionRepository->findOneById('uuid')->willReturn(null);

        $this->service->capture('uuid', Money::GBP('120'));
    }

    /**
     * @test
     */
    public function canCapture()
    {
        $card = new Card();
        $card->topUp(Money::GBP('444'));
        $transaction = $card->getAuthorizationRequest(Money::GBP('120'), 'name');

        $this->transactionRepository->findOneById($transaction->getId())->willReturn($transaction);

        $this->service->capture($transaction->getId(), Money::GBP('120'));
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Transaction [uuid] not found
     */
    public function cannotReverse()
    {
        $this->transactionRepository->findOneById('uuid')->willReturn(null);

        $this->service->reverse('uuid', Money::GBP('120'));
    }

    /**
     * @test
     */
    public function canReverse()
    {
        $card = new Card();
        $card->topUp(Money::GBP('444'));
        $transaction = $card->getAuthorizationRequest(Money::GBP('120'), 'name');

        $this->transactionRepository->findOneById($transaction->getId())->willReturn($transaction);

        $this->service->reverse($transaction->getId(), Money::GBP('120'));
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\ResourceNotFoundException
     * @expectedExceptionMessage Transaction [uuid] not found
     */
    public function cannotRefund()
    {
        $this->transactionRepository->findOneById('uuid')->willReturn(null);

        $this->service->refund('uuid');
    }

    /**
     * @test
     */
    public function canRefund()
    {
        $card = new Card();
        $card->topUp(Money::GBP('444'));
        $transaction = $card->getAuthorizationRequest(Money::GBP('120'), 'name');
        $transaction->capture(Money::GBP('120'));

        $this->transactionRepository->findOneById($transaction->getId())->willReturn($transaction);

        $this->service->refund($transaction->getId());
    }
}
