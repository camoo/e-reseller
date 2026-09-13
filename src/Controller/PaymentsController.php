<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Event\EventInterface;
use CAMOO\Exception\Exception;
use Psr\Http\Message\ResponseInterface;

final class PaymentsController extends AppController
{
    public ?\CAMOO\Controller\Component\SecurityComponent $Security = null;

    public ?\App\Model\Rest\PaymentsRest $PaymentsRest = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->loadRest('PaymentsRest');
    }

    public function beforeAction(EventInterface $event): void
    {
        $this->Security->setConfig('unlockedActions', ['mobileMoney']);
        parent::beforeAction($event);
    }

    public function check(): ResponseInterface
    {
        $this->request->allowMethod(['get']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown error !');
        }

        $paymentId = $this->request->getQuery('payment_id');

        if (empty($paymentId)) {
            return $this->jsonResponse([
                'status' => false,
            ]);

        }

        $appRest = $this->PaymentsRest->newRequest(['payment_id' => $paymentId], false);
        $response = $appRest->send(['::payments', 'check'], false);

        return $this->jsonResponse([
            'status' => !empty($response['success']),
        ]);
    }

    public function mobileMoney(): ResponseInterface
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

        $appRest = $this->PaymentsRest->newRequest($payload);
        $response = $appRest->send(['::payments', 'mobileWallet']);

        if ($response['success']) {
            $this->request->Flash->success($response['message'] ?? 'Paiement initié avec succès');
        }

        return $this->jsonResponse([
            'status' => !empty($response['success']),
            'message' => $response['message'] ?? null,
            'paymentId' => $response['paymentId'] ?? null,
        ]);
    }
}
