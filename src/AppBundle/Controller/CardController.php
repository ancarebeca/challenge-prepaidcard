<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Exception\ParameterNotFoundException;
use Money\Money;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @Route("/cards")
 */
class CardController extends Controller
{
    /**
     * @Route("", name="cards_create")
     * @Method("POST")
     */
    public function createAction()
    {
        $card = $this->get('prepaid_card_app.service.prepaid_card')->createCard();

        return $this->createJsonResponse($card);
    }

    /**
     * @Route("/{cardId}", name="cards_topup")
     * @Method("PATCH")
     */
    public function topUpAction(Request $request)
    {
        if (empty($request->get('cardId'))) {
            throw new ParameterNotFoundException('card_id parameter is missing');
        }

        if (empty($request->get('amount'))) {
            throw new ParameterNotFoundException('amount parameter is missing');
        }

        $prepaidCardServices = $this->get('prepaid_card_app.service.prepaid_card');
        $card = $prepaidCardServices->topUp($request->get('cardId'), Money::GBP($request->get('amount')));

        return $this->createJsonResponse($card);
    }

    /**
     * @Route("/{cardId}", name="cards_retrieve")
     * @Method("GET")
     */
    public function retrieveAction(Request $request)
    {
        if (empty($request->get('cardId'))) {
            throw new ParameterNotFoundException('card_id parameter is missing');
        }

        $prepaidCardServices = $this->get('prepaid_card_app.service.prepaid_card');
        $card = $prepaidCardServices->getCard($request->get('cardId'));

        return $this->createJsonResponse($card);
    }

    /**
     * @Route("/{cardId}/request-authorization", name="cards_request_authorization")
     * @Method("POST")
     */
    public function requestAuthorizationAction(Request $request)
    {
        if (empty($request->get('cardId'))) {
            throw new ParameterNotFoundException('card_id parameter is missing');
        }

        if (empty($request->get('amount'))) {
            throw new ParameterNotFoundException('amount parameter is missing');
        }

        $prepaidCardServices = $this->get('prepaid_card_app.service.prepaid_card');
        $transaction = $prepaidCardServices->requestAuthorization(
            $request->get('cardId'),
            Money::GBP($request->get('amount')),
            $request->get('description', '')
        );

        return $this->createJsonResponse($transaction);
    }

    /**
     * @Route("/{cardId}/statements", name="cards_statements")
     * @Method("GET")
     */
    public function statements(Request $request)
    {
        if (empty($request->get('cardId'))) {
            throw new ParameterNotFoundException('card_id parameter is missing');
        }

        $card = $this->container->get('prepaid_card_app.repository.card')->find($request->get('cardId'));

        return $this->createJsonResponse($card->getTransactions()->toArray());
    }

    private function createJsonResponse($data)
    {
        return new JsonResponse(
            $this->container->get('jms_serializer')->serialize($data, 'json'),
            Response::HTTP_OK,
            ['Content-Type', 'application/json'],
            true
        );
    }
}
