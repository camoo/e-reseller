<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Utils\Configure;
use Camoo\Hosting\Modules\ShowcaseSubscribers;
use Psr\Http\Message\ResponseInterface;

use function Cake\I18n\__;

final class NewsletterController extends AppController
{
    public function subscribe(): ResponseInterface
    {
        $this->request->allowMethod(['post']);

        $isAjax = $this->request->is('ajax')
            || str_contains((string)$this->request->getEnv('HTTP_ACCEPT'), 'application/json');

        $returnPath = $this->getReturnPath();
        $honeypot = trim((string)$this->request->getData('website', ''));
        if ($honeypot !== '') {
            if ($isAjax) {
                return $this->jsonResponse([
                    'status' => true,
                    'success' => true,
                    'message' => __('Merci ! Votre adresse e-mail est maintenant inscrite à notre newsletter.'),
                ]);
            }

            return $this->redirect($returnPath);
        }

        $email = trim((string)$this->request->getData('email', ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = __('Veuillez saisir une adresse e-mail valide.');
            if ($isAjax) {
                return $this->jsonResponse([
                    'status' => false,
                    'success' => false,
                    'message' => $errorMessage,
                ]);
            }

            $this->request->Flash->error($errorMessage);

            return $this->redirect($returnPath);
        }

        try {
            $response = new ShowcaseSubscribers()->subscribe($email, $this->getShowcaseWebsite());
            $responseData = $response->getJson();
            if ($response->getStatusCode() !== 200
                || ($responseData['status'] ?? null) !== 'OK'
                || (($responseData['result']['success'] ?? false) !== true)
            ) {
                throw new \RuntimeException('Showcase subscription was rejected.');
            }
        } catch (\Throwable) {
            $errorMessage = __('Votre inscription à la newsletter n’a pas pu être enregistrée. Veuillez réessayer plus tard.');
            if ($isAjax) {
                return $this->jsonResponse([
                    'status' => false,
                    'success' => false,
                    'message' => $errorMessage,
                ]);
            }

            $this->request->Flash->error($errorMessage);

            return $this->redirect($returnPath);
        }

        $successMessage = __('Merci ! Votre adresse e-mail est maintenant inscrite à notre newsletter.');
        if ($isAjax) {
            return $this->jsonResponse([
                'status' => true,
                'success' => true,
                'message' => $successMessage,
            ]);
        }

        $this->request->Flash->success($successMessage);

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

    /**
     * Identify the showcase that collected the address without trusting the
     * visitor-controlled honeypot field named "website".
     */
    private function getShowcaseWebsite(): string
    {
        // HTTP_HOST identifies the public showcase that received the form.
        // Only fall back to configuration when the request has no host (for
        // example, during a command-line or synthetic invocation).
        $website = trim((string)$this->request->getEnv('HTTP_HOST'));
        if ($website === '') {
            $website = trim((string)$this->request->getEnv('HTTP_X_FORWARDED_HOST'));
        }
        if ($website === '') {
            $configuredWebsite = Configure::read('RESELLER_SITE.domain');
            $website = is_string($configuredWebsite) ? trim($configuredWebsite) : '';
        }

        $website = trim($website);
        if (str_contains($website, '://')) {
            $parsedHost = parse_url($website, PHP_URL_HOST);
            if (is_string($parsedHost) && $parsedHost !== '') {
                $website = $parsedHost;
            }
        }

        $website = preg_replace('/:\d+$/', '', $website) ?? $website;

        return strtolower(trim($website, ". \t\n\r\0\x0B"));
    }
}
