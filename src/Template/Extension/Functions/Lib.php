<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Cache\Cache;
use CAMOO\Template\Extension\FunctionHelper;

/**
 * Class Lib
 *
 * @author CamooSarl
 */
final class Lib extends FunctionHelper
{
    public $functions = ['Basket'];

    public function getFunctions(): array
    {
        return [
            $this->add('domainwhois_results', [$this, 'getDomainWhoisResult'], ['is_safe' => ['html']]),
        ];
    }

    public function getDomainWhoisResult($inp)
    {
        $oBasket = $this->Basket->getItems();
        $result = '';
        if (($xRet = Cache::read($inp, '_camoo_hosting_1hour')) !== false) {
            foreach ($xRet as $domain => $value) {
                $class = 'available domain-available add-to-basket';
                $word = 'Disponible';
                $takeIt = 'Je commande';
                $cmd = 'add-to-basket';
                if ($oBasket->has($domain)) {
                    $cmd .= ' disable';
                    $takeIt = $word = 'Dans le panier';
                }

                if ($value['status'] === 'N') {
                    $class = 'disable domain-taken';
                    $word = 'déjà pris';
                    $cmd = 'disable';
                }
                $result .= sprintf('
                    <div class="single_search d-flex justify-content-between align-items-center">
                        <div class="name_title">
                            <h4>' . $domain . '</h4>
                        </div>
                        <div class="prising_content single-domain-item">
                            <a data-domain="' . $domain . '" data-price="' . $value['price']['addnewdomain'] . '" class="trigger-domain premium %s" href="#">%s</a>
                            <a href="#">XAF ' . $value['price']['addnewdomain'] . '/an</a> 
                            <a data-domain="' . $domain . '" data-price="' . $value['price']['addnewdomain'] . '" class="trigger-domain boxed_btn_green %s" href="#">%s</a>
                        </div>
                    </div>', $class, $word, $cmd, $takeIt);
            }
        }

        return $result;
    }
}
