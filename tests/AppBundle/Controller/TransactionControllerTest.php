<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Card;
use Money\Money;
use Symfony\Component\HttpFoundation\Response;
use Tests\FunctionalTestBase;

/**
 * @group Functional
 */
class TransactionControllerTest extends FunctionalTestBase
{
    /**
     * @test
     */
    public function canCapture()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));
        $transaction = $card->getAuthorizationRequest(Money::GBP("50"), "Costa");

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/%s/captures', $transaction->getId()),
            [
                'amount' => '40',
            ]
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );

        $decodedResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($decodedResponse);
        $this->assertEquals($transaction->getId(), $decodedResponse['id']);
        $this->assertEquals($transaction->getDescription(), $decodedResponse['description']);
        $this->assertEquals('40', $decodedResponse['captured_amount']['amount']);
    }

    /**
     * @test
     */
    public function cannotCapture()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));
        $transaction = $card->getAuthorizationRequest(Money::GBP("50"), "Costa");

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/%s/captures', $transaction->getId()),
            [
                'amount' => '400',
            ]
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @test
     */
    public function canReverse()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));
        $transaction = $card->getAuthorizationRequest(Money::GBP("50"), "Costa");

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/%s/reverses', $transaction->getId()),
            [
                'amount' => '40',
            ]
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );

        $decodedResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($decodedResponse);
        $this->assertEquals($transaction->getId(), $decodedResponse['id']);
        $this->assertEquals($transaction->getDescription(), $decodedResponse['description']);
        $this->assertEquals('40', $decodedResponse['reversed_amount']['amount']);
    }

    /**
     * @test
     */
    public function cannotReverse()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));
        $transaction = $card->getAuthorizationRequest(Money::GBP("50"), "Costa");

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/%s/reverses', $transaction->getId()),
            [
                'amount' => '800',
            ]
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @test
     */
    public function canRefund()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));
        $transaction = $card->getAuthorizationRequest(Money::GBP('50'), "Costa");
        $card->capture($transaction, Money::GBP('50'));

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/%s/refunds', $transaction->getId())
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );

        $decodedResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($decodedResponse);
        $this->assertEquals($transaction->getId(), $decodedResponse['id']);
        $this->assertEquals($transaction->getDescription(), $decodedResponse['description']);
        $this->assertNotEmpty($decodedResponse['refunded_at']);
    }

    /**
     * @test
     */
    public function cannotRefund()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));
        $transaction = $card->getAuthorizationRequest(Money::GBP('50'), "Costa");

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/%s/refunds', $transaction->getId())
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @test
     */
    public function cannotMakeAReverseWithAWrongTransactionId()
    {
        $card = new Card();
        $card->topUp(Money::GBP("100"));

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/transactions/dfgdfg/reverses'),
            [
                'amount' => '40',
            ]
        );

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );
    }
}