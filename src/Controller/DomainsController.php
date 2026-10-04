<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ControllerException;
use App\Lib\DomainName;
use Camoo\Cache\Cache;
use CAMOO\Event\EventInterface;
use CAMOO\Exception\Exception;
use CAMOO\Utils\Configure;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DomainsController
 *
 * @author CamooSarl
 */
class DomainsController extends AppController
{
    public ?\CAMOO\Controller\Component\SecurityComponent $Security = null;

    public ?\App\Model\Rest\DomainsRest $DomainsRest = null;

    public ?\App\Model\Rest\ContactsRest $ContactsRest = null;

    private array $allowedExtensions = [
        'cm',
        'com',
        'net',
        'org',
        'info',
        'biz',
        'site',
        'pro',
        'host',
    ];

    public function initialize(): void
    {
        parent::initialize();
        $this->loadRest('DomainsRest');
    }

    public function beforeAction(EventInterface $event): void
    {
        $this->requireFeature('domains');
        parent::beforeAction($event);
    }

    public function domainSearch(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            $status = false;
            $domain = DomainName::normalize((string)$this->request->getData('domain'));

            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);

            if (!empty($oNewRequest->getErrors())) {
                $this->showValidateErrors($oNewRequest);

                return $this->jsonResponse([
                    'status' => false,
                    'result' => $oNewRequest->getErrors(),
                ]);

            }

            $xRet = Cache::reads($domain, '_camoo_hosting_1hour');
            if ($xRet !== false && !$this->isAvailabilityResult($xRet)) {
                Cache::deletes($domain, '_camoo_hosting_1hour');
                $xRet = false;
            }

            if ($xRet === false) {
                try {
                    $xRet = $oNewRequest->send(['::domains', 'checkAvailability'], false);
                } catch (\Throwable) {
                    $xRet = false;
                }
                if (!$this->isAvailabilityResult($xRet)) {
                    $xRet = $this->localAvailability($domain);
                }

                if ($this->isAvailabilityResult($xRet)) {
                    Cache::writes($domain, $xRet, '_camoo_hosting_1hour');
                }
            }

            $status = $this->isAvailabilityResult($xRet);

            return $this->jsonResponse([
                'status' => $status,
                'domain' => $domain,
            ]);

        }

        throw new Exception('Unknown error !');
    }

    private function isAvailabilityResult(mixed $result): bool
    {
        if (!is_array($result) || $result === []) {
            return false;
        }

        foreach ($result as $details) {
            if (!is_array($details) || !array_key_exists('status', $details)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Provide deterministic domain rows for local development when the
     * hosting provider is not configured. Production never uses this unless
     * explicitly enabled through configuration.
     */
    private function localAvailability(string $domain): array
    {
        $config = Configure::read('DomainAvailability.local');
        if (!is_array($config) || ($config['enabled'] ?? false) !== true) {
            return [];
        }

        $prices = is_array($config['prices'] ?? null) ? $config['prices'] : [];
        $takenTlds = is_array($config['taken_tlds'] ?? null) ? $config['taken_tlds'] : [];
        $result = [];

        foreach ($this->allowedExtensions as $tld) {
            $result[$domain . '.' . $tld] = [
                'status' => in_array($tld, $takenTlds, true) ? 'N' : 'Y',
                'price' => [
                    'addnewdomain' => $prices[$tld] ?? null,
                ],
            ];
        }

        return $result;
    }

    public function overview(): ResponseInterface
    {
        $rawDomain = (string)$this->request->getQuery('d');
        if (empty(trim($rawDomain))) {
            return $this->redirect('/');
        }

        $domain = DomainName::normalize($rawDomain);
        if (empty($domain)) {
            return $this->redirect('/');
        }

        $xRet = Cache::reads($domain, '_camoo_hosting_1hour');
        if ($xRet !== false && !$this->isAvailabilityResult($xRet)) {
            Cache::deletes($domain, '_camoo_hosting_1hour');
            $xRet = false;
        }

        if ($xRet === false) {
            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);
            if (empty($oNewRequest->getErrors())) {
                try {
                    $xRet = $oNewRequest->send(['::domains', 'checkAvailability'], false);
                } catch (\Throwable) {
                    $xRet = false;
                }
                if (!$this->isAvailabilityResult($xRet)) {
                    $xRet = $this->localAvailability($domain);
                }
                if ($this->isAvailabilityResult($xRet)) {
                    Cache::writes($domain, $xRet, '_camoo_hosting_1hour');
                }
            }
        }

        $this->set('domain', $domain);
        $this->set('searchDomain', $rawDomain !== '' ? $rawDomain : $domain);

        return $this->render();
    }

    public function addToBasket(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new ControllerException('Invalid Request type');
        }

        $cart = $this->getBasketRepository();
        $status = false;
        $domain = strtolower(trim((string)$this->request->getData('domain')));
        $domainBasket = $domain;
        $domain = DomainName::normalize($domain);

        $xRet = Cache::reads($domain, '_camoo_hosting_1hour');
        $hDomain = [];
        if ($xRet !== false && is_array($xRet) && is_array($hDomain = $xRet[$domainBasket] ?? null)) {
            $status = true;
            $hDomain['price'] = $hDomain['price']['addnewdomain'] ?? null;
            if ($hDomain['price'] === null) {
                return $this->jsonResponse(['status' => false, 'item' => []]);
            }

            $hDomain['basket_icon'] = 'flaticon-hosting';
            $hDomain['description'] = 'Nom de domaine';
            // other
            //$hDomain['basket_icon'] = 'flaticon-servers';
            $cart->addItem($domainBasket, $hDomain);
        }

        return $this->jsonResponse([
            'status' => $status,
            'item' => $hDomain,
        ]);
    }

    public function removeFromBasket(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new ControllerException('Invalid request');
        }

        $cart = $this->getBasketRepository();
        $domain = strtolower((string)$this->request->getData('domain'));

        $cart->removeItem($domain);

        return $this->jsonResponse([
            'status' => true,
            'item' => $domain,
        ]);
    }

    public function decision(): ResponseInterface
    {
        $this->set('page_title', $this->showcaseText('pages.domain.decision_title', 'Indiquez un nom de domaine'));
        $itemKeyId = $this->request->getQuery('kid');
        if (empty($itemKeyId)) {
            return $this->redirect('/');
        }

        $this->set('item_key', $itemKeyId);
        return $this->render();
    }

    public function isValid(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown error !');
        }

        $domain = (string)$this->request->getData('domain');

        $status = DomainName::isValid($domain);
        if ($status) {
            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);
            $status = empty($oNewRequest->getErrors());
        }

        return $this->jsonResponse([
            'status' => $status,
        ]);
    }

    public function editContact(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        $this->loadRest('ContactsRest');

        $data = $this->request->getData();
        $oNewRequest = $this->ContactsRest->newRequest($data, true, ['validation' => 'edit']);

        if (!empty($oNewRequest->getErrors())) {
            $this->showValidateErrors($oNewRequest);

            if ($this->request->is('ajax')) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $oNewRequest->getErrors(),
                ]);
            }

            return $this->redirect('/');
        }

        $result = $oNewRequest->send(['::contacts', 'edit']);

        if ($this->request->is('ajax')) {
            return $this->jsonResponse([
                'status' => true,
                'result' => $result,
            ]);
        }

        $this->request->Flash->success('Contact mis à jour avec succès');

        return $this->redirect('/');
    }

    public function resendVerification(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        $id = (int)$this->request->getData('id');

        $oNewRequest = $this->DomainsRest->newRequest(['id' => $id], true, ['validation' => 'resendVerification']);

        if (!empty($oNewRequest->getErrors())) {
            $this->showValidateErrors($oNewRequest);

            if ($this->request->is('ajax')) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $oNewRequest->getErrors(),
                ]);
            }

            return $this->redirect('/');
        }

        $response = $oNewRequest->send(['::domains', 'resendVerificationMail'], false);

        if ($this->request->is('ajax')) {
            return $this->jsonResponse([
                'status' => true,
                'message' => 'Email de vérification renvoyé avec succès',
                'result' => $response,
            ]);
        }

        $this->request->Flash->success('Email de vérification renvoyé avec succès');

        return $this->redirect('/');
    }
}
