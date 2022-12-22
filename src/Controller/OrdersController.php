<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use CAMOO\Utils\Cart;

final class OrdersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadRest('OrderRest');
    }

    public function beforeAction(Event $event)
    {
        $this->Security->setConfig('unlockedActions', ['payOffline']);
        parent::beforeAction($event);
    }

    public function payOffline(): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown error !');
        }
        $cart = $this->getBasketRepository();

        $oderRequest = $this->OrderRest->newRequest(['body' => json_encode($this->buildCartData($cart))]);

        if (!empty($oderRequest->getErrors())) {
            $this->showValidateErrors($oderRequest);

            $this->_jsonResponse([
                'status' => false,
                'result' => $oderRequest->getErrors(),
            ]);
        }
        $response = $oderRequest->send(['::orders', 'offline']);

        if (!empty($response['success'])) {
            $this->request->Flash->success('Commande effectuée avec succès');
            $cart->delete();
        } else {
            $this->request->Flash->error('Échec de commande. Veuillez vérifier votre panier et re-essayer plus tard.');
        }

        $this->_jsonResponse([
            'status' => !empty($response['success']),
        ]);
    }

    public function payWithMobileWallet(): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown Error on online payment!');
        }

        $paymentId = $this->request->getData('payment_id');
        $cart = $this->getBasketRepository();

        $oderRequest = $this->OrderRest->newRequest(['body' => json_encode($this->buildCartData($cart, $paymentId))]);

        if (!empty($oderRequest->getErrors())) {
            $this->showValidateErrors($oderRequest);

            $this->_jsonResponse([
                'status' => false,
                'result' => $oderRequest->getErrors(),
            ]);
        }
        $response = $oderRequest->send(['::orders', 'online']);

        if (!empty($response['success'])) {
            $this->request->Flash->success('Commande effectuée avec succès');
            $cart->delete();
        } else {
            $this->request->Flash->error('Échec de commande. Veuillez vérifier votre panier et re-essayer plus tard.');
        }

        $this->_jsonResponse([
            'status' => !empty($response['success']),
        ]);
    }

    private function buildCartData(Cart $cart, ?string $paymentId = null): array
    {
        $cartData = [];
        foreach ($cart as $type => $items) {
            if ($type !== 'hosting') {
                if (array_key_exists('classkey', $items)) {
                    $cartData['domain'][$items['classkey']] = [
                        $type => $items['price'],
                    ];
                }
                continue;
            }
            foreach ($items as $item) {
                if (array_key_exists('id', $item)) {
                    $cartData[$type]['id'][$item['sku']] = null;
                }

                if (array_key_exists('domain_hosting', $item)) {
                    $cartData[$type]['id'][$item['sku']] = $item['domain_hosting'];
                }
            }
        }

        $cartData['total_price'] = $cart->getTotalPrice();
        $cartData['item_count'] = $cart->count();
        $cartData['user'] = $this->getUserId();
        if (null !== $paymentId) {
            $cartData['payment_id'] = $paymentId;
        }

        return $cartData;
    }
}
