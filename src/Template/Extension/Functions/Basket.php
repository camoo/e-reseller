<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Template\Extension\FunctionHelper;
use CAMOO\Utils\Cart as BasketRepository;

/**
 * Class Basket
 *
 * @author CamooSarl
 */
final class Basket extends FunctionHelper
{
    /** @var array $_basket */
    private $_basket = [BasketRepository::class, 'create'];

    public function getFunctions(): array
    {
        return [
            $this->add('basket_counter', [$this, 'getCount']),
            $this->add('basket_items', [$this, 'getItems']),
        ];
    }

    public function getCount(): int
    {
        return $this->getBasketRepository()->count();
    }

    public function getItems(): BasketRepository
    {
        return $this->getBasketRepository();
    }

    private function getBasketRepository(): BasketRepository
    {
        return call_user_func($this->_basket, $this->request);
    }
}
