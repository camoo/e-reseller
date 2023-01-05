<?php

declare(strict_types=1);

namespace App\Template\Extension;

use App\Template\Extension\Functions\Basket;
use App\Template\Extension\Functions\Lib;
use App\Template\Extension\Functions\Tariffs;
use App\Template\Extension\Functions\Users;
use CAMOO\Template\Extension\Functions;

/**
 * Class AppFunctions
 *
 * @author CamooSarl
 */
class AppFunctions extends Functions
{
    public function initialize(): void
    {
        $this->load(Users::class);
        $this->load(Tariffs::class);
        $this->load(Lib::class);
        $this->load(Basket::class);
    }
}
