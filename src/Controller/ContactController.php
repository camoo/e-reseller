<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Mailer\Mailer;

/**
 * Class ContactController
 * @author CammoSarl
 */
class ContactController extends AppController
{
    public function overview()
    {
        if ($this->request->is('post')) {
            $message = $this->request->getData('message');
            $subject = $this->request->getData('subject');
            $name = $this->request->getData('name');
            $replyTo = $this->request->getData('email');
            $oEmail = new Mailer();
            $oEmail->addTo($adminEmail)
                    ->setSubject($subject)
                    ->addReplyTo($replyTo)
                    ->setBody($message);
            $oEmail->send();
            $this->request->Flash->success('Merci! Nous vous contacterons dès que possible');
        }

        $this->render();
    }
}
