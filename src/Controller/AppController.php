<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lib\Traits\UserDataTrait;
use App\Model\Rest\DomainsRest;
use CAMOO\Controller\AppController as BaseController;
use CAMOO\Controller\Component\SecurityComponent;
use CAMOO\Interfaces\RestInterface;
use CAMOO\Model\AppModel;
use CAMOO\Utils\Cart;
use CAMOO\Utils\Configure;

/**
 * @property SecurityComponent $Security
 * @property DomainsRest       $DomainsRest
 */
class AppController extends BaseController
{
    use UserDataTrait;

    private array $_basket = [Cart::class, 'create'];

    public function initialize(): void
    {
        parent::initialize();
        $this->set('siteConfig', Configure::read('RESELLER_SITE'));
        $this->loadComponent('Security');
    }

    protected function getBasketRepository(): Cart
    {
        $cart = call_user_func($this->_basket, $this->request);
        if ($this->request->getSession()->check('loggedin')) {
            $cart->setUserId($this->getUserId());
        }
        $cart->refresh();

        return $cart;
    }

    protected function getPackageById(int $id): ?array
    {
        $ahTariffs = Configure::read('RESELLER_TARIFFS');
        $tariff = array_filter($ahTariffs['tariffs'], static function (array $hTariff) use ($id) {
            if ($id === $hTariff['id']) {
                return $hTariff;
            }

            return null;
        });
        $tariff = array_values($tariff);

        return array_shift($tariff) ?? null;
    }

    /** @param AppModel|RestInterface $model */
    protected function showValidateErrors($model, string $flashType = 'error'): void
    {
        if (empty($model)) {
            return;
        }
        $ahErrors = $model->getErrors();
        $asFields = [];
        if (!empty($ahErrors)) {
            foreach ($ahErrors as $sField => $ahError) {
                $asFields[] = $sField;
                foreach ($ahError as $sMessage) {
                    $this->request->Flash->{$flashType}($sMessage);
                }
            }
            if (!empty($asFields)) {
                $this->set('errorFields', $asFields);
            }
        }
    }
}
