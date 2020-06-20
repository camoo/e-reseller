<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use CAMOO\Utils\Inflector;

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
            $sku = $this->request->getData('sku');
            $keyItem = $this->request->getData('key');
            $type = $this->request->getData('type');

            $package = $this->getPackageById((int) $sku);
            $ahCartTypeItems = !$oBasket->has($type)? [] : $oBasket->get($type);
            $sNewId = uniqid($sku, false);
            $ahCartTypeItems[] = [
                'belongs'     => $keyItem,
                'sku'         => $sku,
                'id'          => $sNewId,
                'price'       => $package['price'],
                'basket_icon' => 'flaticon-servers',
                'human_name'  => Inflector::classify($package['name']),
                'name'        => $package['name'],
                'description' => $package['desc_short'],
                'package'     => $package,
            ];
           
            try {
                // REMOVE OLD KEY
                $oBasket->removeItem($type);

                // UPDATE KEY
                $oBasket->addItem($type, $ahCartTypeItems);
            } catch (Exception $exception) {
                $status = false;
            }

            return $this->_jsonResponse(['status' => $status, 'id' => $sNewId]);
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

            $id = $this->request->getData('id');
            $type = $this->request->getData('type');
            if (!empty($id) && !empty($type) && $type === 'hosting') {
                $itemKey = $this->getItemKeyId($id); // key might be 0 as well
                $ahCartTypeItems = $oBasket->get('hosting');
                unset($ahCartTypeItems[$itemKey]);
 
                // REMOVE OLD KEY
                $oBasket->removeItem('hosting');

                if (!empty($ahCartTypeItems)) {
                    // UPDATE KEY
                    $oBasket->addItem($type, $ahCartTypeItems);
                }
            } else {
                $oBasket->removeItem($sku);
            }

            return $this->_jsonResponse(['status' => $status]);
        }
    }

    public function addDomainToHosting()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            /** @var Cart **/
            $oBasket = $this->getBasketRepository();
            $status = true;
            $id = $this->request->getData('id');
            $domain = $this->request->getData('domain');
            if ($itemKey = $this->getItemKeyId($id)) {
                $ahCartTypeItems = $oBasket->get('hosting');
                $ahCartTypeItems[$itemKey]['on_domain'] = $domain;
                // UPDATE KEY
                $oBasket->addItem($type, $ahCartTypeItems);
            }
            return $this->_jsonResponse([
                        'status' => $status,
                        //'item' => $sku,
                    ]);
        }
    }

    /**
     * @param string $id
     * @param string $type
     * @return null|int
     */
    protected function getItemKeyId(string $id, string $type='hosting') : ?int
    {
        /** @var Cart **/
        $oBasket = $this->getBasketRepository();
 
        if (!$oBasket->has($type)) {
            return [];
        }
            
        $ahCartTypeItems = $oBasket->get($type);
        $cartItem = array_filter($ahCartTypeItems, static function ($item) use ($id) {
            if ($id === $item['id']) {
                return $item;
            }
        });
        if ($cartItem && ($key = array_key_first($cartItem)) !== null) {
            return $key;
        }
        return null;
    }
}
