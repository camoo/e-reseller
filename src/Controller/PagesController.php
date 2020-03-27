<?php
declare(strict_types=1);
namespace App\Controller;

use CAMOO\Utils\Configure;
use CAMOO\Cache\Filesystem;
use CAMOO\Event\Event;

class Pagescontroller extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Users');
    }

    public function overview()
    {
        $this->set('title', 'Welcome to Camoo');
        $this->set('description', 'Camoo Sarl PHP Framework');
        $this->set('keywords', 'Web hosting api, Reseller api, Hebergement web');
        $this->render();
    }
}
