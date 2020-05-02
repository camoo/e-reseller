<?php

namespace App\Template\Extension\Functions;

use App\Lib\Utils\Basket as BasketRepository;
use CAMOO\Template\Extension\FunctionHelper;
/**
 * Class Basket
 * @author CamooSarl
 */
class Basket extends FunctionHelper
{
    private $_basket = [\App\Lib\Utils\Basket::class, 'create'];

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
        ];
    }

	public function getCount() : int
	{
		return $this->getBasketRepository()->count();
	}
}
