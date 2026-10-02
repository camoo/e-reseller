<?php

declare(strict_types=1);

namespace App\Controller;

use Camoo\Hosting\Modules\ShowcaseSubscribers;
use Psr\Http\Message\ResponseInterface;

use function Cake\I18n\__;

final class NewsletterController extends AppController
{
    public function subscribe(): ResponseInterface
    {
        $this->request->allowMethod(['post']);

        $returnPath = $this->getReturnPath();
        $honeypot = trim((string)$this->request->getData('website', ''));
        if ($honeypot !== '') {
            return $this->redirect($returnPath);
        }

        $email = trim((string)$this->request->getData('email', ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->request->Flash->error(__('Veuillez saisir une adresse e-mail valide.'));

            return $this->redirect($returnPath);
        }

        try {
            $response = (new ShowcaseSubscribers())->subscribe($email);
            $responseData = $response->getJson();
            if ($response->getStatusCode() !== 200
                || ($responseData['status'] ?? null) !== 'OK'
                || (($responseData['result']['success'] ?? false) !== true)
            ) {
                throw new \RuntimeException('Showcase subscription was rejected.');
            }
        } catch (\Throwable) {
            $this->request->Flash->error(__('Votre inscription à la newsletter n’a pas pu être enregistrée. Veuillez réessayer plus tard.'));

            return $this->redirect($returnPath);
        }

        $this->request->Flash->success(__('Merci ! Votre adresse e-mail est maintenant inscrite à notre newsletter.'));

        return $this->redirect($returnPath);
    }

    private function getReturnPath(): string
    {
        $referer = $this->request->getReferer() ?? '';
        $path = parse_url($referer, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        $query = parse_url($referer, PHP_URL_QUERY);

        return $path . (is_string($query) && $query !== '' ? '?' . $query : '');
    }
}
