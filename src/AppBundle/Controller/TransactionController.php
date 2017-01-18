<?php

namespace AppBundle\Controller;

use AppBundle\Exception\ParameterNotFoundException;
use Money\Money;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/transactions")
 */
class TransactionController extends Controller
{

    /**
     * @Route("/{transactionId}/captures", name="transactions_capture")
     * @Method("POST")
     */
    public function captureAction(Request $request)
    {
        if (empty($request->get('amount'))) {
            throw new ParameterNotFoundException('amount parameter is missing');
        }

        $prepaidCardServices = $this->get('prepaid_card_app.service.prepaid_card');
        $transaction = $prepaidCardServices->capture(
            $request->get('transactionId'),
            Money::GBP($request->get('amount'))
        );

        return $this->createJsonResponse($transaction);
    }

    /**
     * @Route("/{transactionId}/reverses", name="transactions_reverse")
     * @Method("POST")
     */
    public function reverseAction(Request $request)
    {
        if (empty($request->get('amount'))) {
            throw new ParameterNotFoundException('amount parameter is missing');
        }

        if (empty($request->get('transactionId'))) {
            throw new ParameterNotFoundException('transactionId parameter is missing');
        }

        $prepaidCardServices = $this->get('prepaid_card_app.service.prepaid_card');
        $transaction = $prepaidCardServices->reverse(
            $request->get('transactionId'),
            Money::GBP($request->get('amount'))
        );

        return $this->createJsonResponse($transaction);
    }

    /**
     * @Route("/{transactionId}/refunds", name="transactions_refund")
     * @Method("POST")
     */
    public function refund(Request $request)
    {
        if (empty($request->get('transactionId'))) {
            throw new ParameterNotFoundException('transactionId parameter is missing');
        }

        $prepaidCardServices = $this->get('prepaid_card_app.service.prepaid_card');
        $transaction = $prepaidCardServices->refund(
            $request->get('transactionId')
        );

        return $this->createJsonResponse($transaction);
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
