<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Event\EventInterface;
use CAMOO\Exception\Exception;
use Camoo\Inflector\Inflector;
use function Cake\I18n\__;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BasketController
 *
 * @author CamooSarl
 */
final class BasketController extends AppController
{
    public ?\CAMOO\Controller\Component\SecurityComponent $Security = null;

    public function beforeAction(EventInterface $event): void
    {
        $this->Security->setConfig('unlockedActions', ['add', 'delete']);
        parent::beforeAction($event);
    }

    public function overview(): ResponseInterface
    {
        $this->set('page_title', __('Votre Panier'));
        $cart = $this->getBasketRepository();
        $this->set('basket', $cart);
        return $this->render();
    }

    public function add(): ResponseInterface
    {
        $this->request->allowMethod(['post']);

        if (!$this->request->is('ajax')) {
            throw new \RuntimeException('Invalid Request type');
        }

        $cart = $this->getBasketRepository();
        $status = true;
        $sku = $this->request->getData('sku');
        $keyItem = $this->request->getData('key');
        $type = $this->request->getData('type');
        $actionKey = $this->request->getData('action_key');

        $package = $this->getPackageById((int)$sku);
        if ($package === null) {
            return $this->jsonResponse(['status' => false, 'id' => null]);
        }

        $ahCartTypeItems = !$cart->has($type) ? [] : $cart->get($type);
        $sNewId = uniqid($sku, false);
        if (null !== $actionKey && !empty($ahCartTypeItems)) {
            foreach ($ahCartTypeItems as &$ahCartTypeItem) {
                if ((string)($ahCartTypeItem['sku'] ?? '') !== (string)$sku) {
                    continue;
                }

                $sNewId = $ahCartTypeItem['id'];
                $ahCartTypeItem[$actionKey] = $keyItem;
            }
        } else {
            $ahCartTypeItems[] = [
                'belongs' => $keyItem,
                'sku' => $sku,
                'id' => $sNewId,
                'price' => $package['price'],
                'basket_icon' => 'flaticon-servers',
                'human_name' => Inflector::classify($package['name']),
                'name' => $package['name'],
                'description' => $package['desc_short'],
                'package' => $package,
            ];
        }

        try {
            // REMOVE OLD KEY
            $cart->removeItem($type);

            // UPDATE KEY
            $cart->addItem($type, $ahCartTypeItems);
        } catch (Exception) {
            $status = false;
        }

        return $this->jsonResponse(['status' => $status, 'id' => $sNewId]);
    }

    public function delete(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new \RuntimeException('Invalid Request type');
        }

        $cart = $this->getBasketRepository();
        $sku = $this->request->getData('sku');
        $id = $this->request->getData('id');
        $type = $this->request->getData('type');
        if (!empty($id) && !empty($type) && $type === 'hosting') {
            $itemKey = $this->getItemKeyId($id); // key might be 0 as well
            $ahCartTypeItems = $cart->get('hosting');
            unset($ahCartTypeItems[$itemKey]);

            // REMOVE OLD KEY
            $cart->removeItem('hosting');

            if (!empty($ahCartTypeItems)) {
                // UPDATE KEY
                $cart->addItem($type, $ahCartTypeItems);
            }
        } else {
            $cart->removeItem((string)$sku);
        }

        return $this->jsonResponse(['status' => true]);
    }

    public function addDomainToHosting(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Invalid Action');
        }

        $cart = $this->getBasketRepository();

        $id = $this->request->getData('id');
        $domain = $this->request->getData('domain');
        $itemKey = $this->getItemKeyId($id);
        if ($itemKey !== null) {
            $ahCartTypeItems = $cart->get('hosting');
            $ahCartTypeItems[$itemKey]['domain_hosting'] = $domain;
            // UPDATE KEY
            $cart->removeItem('hosting');
            $cart->addItem('hosting', $ahCartTypeItems);
        }

        return $this->jsonResponse([
            'status' => true,
        ]);
    }

    protected function getItemKeyId(string $id, string $type = 'hosting'): ?int
    {
        $cart = $this->getBasketRepository();

        if (!$cart->has($type)) {
            return null;
        }

        $ahCartTypeItems = $cart->get($type);
        $cartItem = array_filter($ahCartTypeItems, static function (array $item) use ($id): ?array {
            if ($id === ($item['id'] ?? null)) {
                return $item;
            }

            return null;
        });
        if ($cartItem && ($key = array_key_first($cartItem)) !== null) {
            return $key;
        }

        return null;
    }
}
