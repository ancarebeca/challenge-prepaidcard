<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Card;
use Money\Money;
use Symfony\Component\HttpFoundation\Response;
use Tests\FunctionalTestBase;

/**
 * @group Functional
 */
class CardControllerTest extends FunctionalTestBase
{
    /**
     * @test
     */
    public function createCard()
    {
        $this->client->request('POST', '/cards');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertEquals('0', $data['balance']['amount']);
        $this->assertEquals('0', $data['blocked_amount']['amount']);
        $this->assertEmpty($data['transactions']);
        $this->assertNotEmpty($data['id']);
    }

    /**
     * @test
     */
    public function canTopUpCard()
    {
        $card = new Card();
        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'PATCH',
            sprintf('/cards/%s', $card->getId()),
            [
                'amount' => '40',
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertEquals('40', $data['balance']['amount']);
        $this->assertEquals('0', $data['blocked_amount']['amount']);
        $this->assertCount(1, $data['transactions']);
        $this->assertEquals($data['id'], $card->getId());
    }

    /**
     * @test
     */
    public function canRetrieveCard()
    {
        $card = new Card();
        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'GET',
            sprintf('/cards/%s', $card->getId())
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertEquals('0', $data['balance']['amount']);
        $this->assertEquals('0', $data['blocked_amount']['amount']);
        $this->assertEmpty($data['transactions']);
        $this->assertEquals($data['id'], $card->getId());
    }

    /**
     * @test
     */
    public function canMakeARequestAuthorization()
    {
        $card = new Card();
        $card->topUp(Money::GBP('500'));

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/cards/%s/request-authorization', $card->getId()),
            [
                'amount' => '400',
                'description' => 'Costa',
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertEquals('400', $data['amount']['amount']);
        $this->assertEquals('Costa', $data['description']);
        $this->assertEquals('0', $data['captured_amount']['amount']);
        $this->assertEquals('0', $data['reversed_amount']['amount']);

    }

    /**
     * @test
     */
    public function cannotMakeARequestAuthorization()
    {
        $card = new Card();
        $card->topUp(Money::GBP('500'));

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/cards/%s/request-authorization', $card->getId()),
            [
                'amount' => '700',
                'merchantName' => 'Costa',
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function cannotMakeARequestAuthorizationWithAWrongCardId()
    {
        $card = new Card();
        $card->topUp(Money::GBP('500'));

        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request(
            'POST',
            sprintf('/cards/23423423/request-authorization'),
            [
                'amount' => '700',
                'merchantName' => 'Costa',
            ]
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function canSeeCardStatements()
    {
        $card = new Card();
        $card->topUp(Money::GBP('500'));
        $this->client->getContainer()->get('prepaid_card_app.repository.card')->save($card);

        $this->client->request('GET', sprintf('/cards/%s/statements', $card->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }
}