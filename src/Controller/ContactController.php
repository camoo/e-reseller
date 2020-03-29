<?php

namespace App\Controller;

/**
 * Class ContactController
 * @author CammoSarl
 */
class ContactController extends AppController
{
    public function overview()
    {
        if ($this->request->is('post')) {
        }
        $this->render();
    }
}
