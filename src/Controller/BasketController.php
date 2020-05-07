<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Event\Event;

/**
 * Class BasketController
 * @author CamooSarl
 */
final class BasketController extends AppController
{
    public function initialize() : void
    {
        parent::initialize();
    }

    public function beforeAction(Event $event)
    {
        $this->Security->setConfig('unlockedActions', ['add', 'delete']);
        parent::beforeAction($event);
    }

    public function overview()
    {
        $this->set('page_title', 'Votre Panier');
        $cart = $this->getBasketRepository();
        $this->set('basket', $cart);
        $this->render();
    }

    public function add()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            /** @var Cart **/
            $oBasket = $this->getBasketRepository();
            $status = true;
            $domain = $this->request->getData('domain');
            $domain = strtolower($domain);
            $oBasket->removeItem($domain);

            return $this->_jsonResponse([
                        'status' => $status,
                        'item' => $domain
                    ]);
        }
    }

    public function delete()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            /** @var Cart **/
            $oBasket = $this->getBasketRepository();
            $status = true;
            $sku = $this->request->getData('sku');
            $oBasket->removeItem($sku);

            return $this->_jsonResponse([
                        'status' => $status,
                        'item' => $sku,
                    ]);
        }
    }
}
