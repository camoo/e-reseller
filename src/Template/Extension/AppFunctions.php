<?php

declare(strict_types=1);

namespace App\Template\Extension;

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
        $this->load('Users');
        $this->load('Tariffs');
        $this->load('Lib');
        $this->load('Basket');
    }
}
