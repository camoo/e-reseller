<?php

declare(strict_types=1);

namespace App\Controller;

class PagesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Users');
    }

    public function overview()
    {
        $this->render();
    }

    public function showBasket()
    {
        $this->set('page_title', 'Votre panier');
    }
}
