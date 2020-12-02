<?php
declare(strict_types=1);
namespace App\Controller;

use CAMOO\Controller\AppController as BaseController;
use CAMOO\Utils\Configure;
use CAMOO\Utils\Cart;

class AppController extends BaseController
{

    /** @var array $_basket */
    private $_basket = [Cart::class, 'create'];

    public function initialize() : void
    {
        parent::initialize();
        //debug(Configure::read('RESELLER_TARIFFS'));
        //debug($this->getPackageById());
        $this->set('siteConfig', Configure::read('RESELLER_SITE'));
        $this->loadComponent('Security');
    }

    /**
     * @return Cart
     */
    protected function getBasketRepository() : Cart
    {
        $cart = call_user_func($this->_basket, $this->request);
        if ($this->request->getSession()->check('loggedin')) {
            $cart->setUserId($this->request->getSession()->read('Auth.User.id'));
        }
        $cart->refresh();
        return $cart;
    }

    protected function getPackageById(int $id) : ?array
    {
        $ahTariffs  = Configure::read('RESELLER_TARIFFS');
        $tariff = array_filter($ahTariffs['tariffs'], static function ($hTariff) use ($id) {
            if ($id === $hTariff['id']) {
                return $hTariff;
            }
        });
        $tariff = array_values($tariff);
        return array_shift($tariff)??null;
    }

    /**
     * @param \CAMOO\Model\AppModel|\CAMOO\Interfaces\RestInterface $model
     * @param string $flashType
     *
     * @return void
     */
    protected function showValidateErrors($model, string $flashType = 'error') : void
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
            if (count($asFields)>0) {
                $this->set('errorFields', $asFields);
            }
        }
    }
}
