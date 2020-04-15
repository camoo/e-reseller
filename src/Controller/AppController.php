<?php
declare(strict_types=1);
namespace App\Controller;

use CAMOO\Controller\AppController as BaseController;
use CAMOO\Utils\Configure;

class AppController extends BaseController
{
    public function initialize() : void
    {
        parent::initialize();
        //       debug(Configure::read('RESELLER_TARIFFS'));
        $this->set('siteConfig', Configure::read('RESELLER_SITE'));
    }

}
