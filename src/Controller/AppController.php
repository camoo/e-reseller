<?php
declare(strict_types=1);
namespace App\Controller;

use CAMOO\Controller\AppController as BaseController;
use CAMOO\Utils\Configure;
use App\Lib\Utils\Basket;

class AppController extends BaseController
{
    private $_basket = [\App\Lib\Utils\Basket::class, 'create'];

    public function initialize() : void
    {
        parent::initialize();
        //       debug(Configure::read('RESELLER_TARIFFS'));
        $this->set('siteConfig', Configure::read('RESELLER_SITE'));
        $this->loadComponent('Security');
    }

    /**
     * @return Basket
     */
    protected function getBasketRepository() : Basket
    {
        return call_user_func($this->_basket, $this->request);
    }
}
