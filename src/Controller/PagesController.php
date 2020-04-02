<?php
declare(strict_types=1);
namespace App\Controller;

use CAMOO\Utils\Configure;
use CAMOO\Cache\Filesystem;
use CAMOO\Event\Event;

class Pagescontroller extends AppController
{
    public function initialize() : void
    {
        parent::initialize();
        $this->loadModel('Users');
    }

    public function overview()
    {
        $this->render();
    }
}
