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
        $this->set('page_title', 'Contact');

		#debug($this->request->session->get('csrf_camoo'));
        if ($this->request->is('post')) {
          #  debug($this->request->getData('email'));
         #   die;
        }
        $this->render();
    }
}
