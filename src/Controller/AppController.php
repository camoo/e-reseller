<?php
declare(strict_types=1);
namespace App\Controller;

use CAMOO\Controller\AppController as BaseController;
use CAMOO\Utils\Configure;
use CAMOO\Utils\Cart;

class AppController extends BaseController
{
    private $_basket = [\CAMOO\Utils\Cart::class, 'create'];

    public function initialize() : void
    {
        parent::initialize();
        //       debug(Configure::read('RESELLER_TARIFFS'));
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
            $cart->setUserId($this->request->getSession('Auth.User.id'));
        }
        return $cart;
    }
}
