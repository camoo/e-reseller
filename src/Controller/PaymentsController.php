<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;

final class PaymentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadRest('PaymentsRest');
    }

    public function beforeAction(Event $event)
    {
        $this->Security->setConfig('unlockedActions', ['mobileMoney']);
        parent::beforeAction($event);
    }

    public function check(): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown error !');
        }

        $paymentId = $this->request->getQuery('payment_id');

        if (empty($paymentId)) {
            $this->_jsonResponse([
                'status' => false,
            ]);
        }

        $paymentRequest = $this->PaymentsRest->newRequest(['payment_id' => $paymentId], false);
        $response = $paymentRequest->send(['::payments', 'check'], false);

        $this->_jsonResponse([
            'status' => !empty($response['success']),
        ]);
    }

    public function mobileMoney(): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown error !');
        }

        $payload = [
            'phoneNumber' => $this->request->getData('phoneNumber'),
            'amount' => $this->getBasketRepository()->getTotalPrice(),
            'customer' => $this->getUserId(),
        ];

        $paymentRequest = $this->PaymentsRest->newRequest($payload);
        $response = $paymentRequest->send(['::payments', 'mobileWallet']);

        if ($response['success']) {
            $this->request->Flash->error($response['message']);
        }

        $this->_jsonResponse([
            'status' => !empty($response['success']),
            'message' => $response['message'] ?? null,
            'paymentId' => $response['paymentId'] ?? null,
        ]);
    }
}
