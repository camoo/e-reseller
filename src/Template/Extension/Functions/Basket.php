<?php
declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Utils\Cart as BasketRepository;
use CAMOO\Template\Extension\FunctionHelper;

/**
 * Class Basket
 * @author CamooSarl
 */
final class Basket extends FunctionHelper
{
    private $_basket = [\CAMOO\Utils\Cart::class, 'create'];

    /**
     * @return BasketRepository
     */
    private function getBasketRepository() : BasketRepository
    {
        return call_user_func($this->_basket, $this->request);
    }

    public function getFunctions() : array
    {
        return [
           $this->add('basket_counter', [$this, 'getCount']),
           $this->add('basket_items', [$this, 'getItems']),
        ];
    }

    public function getCount() : int
    {
        return $this->getBasketRepository()->count();
    }

    public function getItems()
    {
        return $this->getBasketRepository();
    }
}
