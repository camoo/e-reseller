<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Mailer\Mailer;
use CAMOO\Utils\Configure;

/**
 * Class ContactController
 *
 * @author CammoSarl
 */
class ContactController extends AppController
{
    public function overview()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            if ($this->isValidEmail($email)) {
                $this->sendEmail();
                $this->request->Flash->success('Merci! Nous vous contacterons dès que possible');
            }
            $this->request->Flash->error('Votre demande ne peut pas être traitée pour le moment');
        }

        $this->render();
    }

    protected function sendEmail(): void
    {
        $message = $this->request->getData('message');
        $subject = $this->request->getData('subject');
        // $name = $this->request->getData('name');
        $replyTo = $this->request->getData('email');
        $mailer = new Mailer();
        $baseUrl = $this->request->getEnv('HTTP_HOST');

        $message .= "\n\n\n*************************************************\n\n" . Configure::read('RESELLER_SITE.title_for_layout') . "\n" .
            $baseUrl . "\n\n Contact us \n\n*************************************************";

        $adminEmail = Configure::read('RESELLER_SITE.contact_email');
        $mailer->addTo($adminEmail)
            ->setSubject($subject)
            ->addReplyTo($replyTo)
            ->setBody($message);
        $mailer->send();
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
