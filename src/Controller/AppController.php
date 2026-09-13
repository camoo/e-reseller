<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lib\Traits\UserDataTrait;
use App\Model\Rest\DomainsRest;
use App\Model\Rest\OrderRest;
use App\Model\Rest\PaymentsRest;
use CAMOO\Controller\AppController as BaseController;
use CAMOO\Controller\Component\SecurityComponent;
use CAMOO\Interfaces\RestInterface;
use CAMOO\Model\AppModel;
use CAMOO\Utils\Cart;
use CAMOO\Utils\Configure;

/**
 * @property SecurityComponent $Security
 * @property DomainsRest       $DomainsRest
 * @property PaymentsRest      $PaymentsRest
 * @property OrderRest         $OrderRest
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
        $tariffs = is_array($ahTariffs) && is_array($ahTariffs['tariffs'] ?? null)
            ? $ahTariffs['tariffs'] : [];

        foreach ($tariffs as $tariff) {
            if (is_array($tariff) && (int)($tariff['id'] ?? -1) === $id) {
                return $tariff;
            }
        }

        return null;
    }

    protected function isFeatureEnabled(string $feature): bool
    {
        $features = Configure::read('Features');

        return is_array($features) && ($features[$feature] ?? false) === true;
    }

    protected function requireFeature(string $feature): void
    {
        if (!$this->isFeatureEnabled($feature)) {
            throw new App\Exception\ControllerException('This product is not available.');
        }
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

            if ($asFields !== []) {
                $this->set('errorFields', $asFields);
            }
        }
    }
}
