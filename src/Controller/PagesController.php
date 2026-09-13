<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

class PagesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Users');
    }

    public function overview(): ResponseInterface
    {
        return $this->render();
    }

    public function showBasket(): ResponseInterface
    {
        $this->set('page_title', 'Votre panier');

        return $this->render();
    }
}
