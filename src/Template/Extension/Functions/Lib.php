<?php

namespace App\Template\Extension\Functions;

use CAMOO\Template\Extension\FunctionHelper;
use CAMOO\Cache\Cache;

/**
 * Class Lib
 * @author CamooSarl
 */
class Lib extends FunctionHelper
{
    public function getFunctions() : array
    {
        return [
            $this->add('domainwhois_results', [$this, 'getDomainWhoisResult'], ['is_safe' => ['html']]),
        ];
    }

    public function getDomainWhoisResult($inp)
    {
        $result = '';
        if (($xRet = Cache::read($inp, '_camoo_hosting_1hour')) !== false) {
            foreach ($xRet as $domain => $value) {
                $class = 'available domain-available';
                $word = 'Disponible';
                $cmd = 'add-to-basket';
                if ($value['status'] === 'N') {
                    $class = 'disable domain-taken';
                    $word = 'déjà pris';
                $cmd = 'disable';
                }
                $result .= sprintf('
                    <div class="single_search d-flex justify-content-between align-items-center">
                        <div class="name_title">
                            <h4>'.$domain.'</h4>
                        </div>
                        <div class="prising_content">
                            <a class="premium %s" href="#">%s</a>
                            <a href="#">XAF '.$value['price']['addnewdomain'].'/an</a> 
                            <a class="boxed_btn_green %s" href="#">Je commande</a>
                        </div>
                    </div>', $class, $word, $cmd);
            }
        }
        return $result;
    }
}
