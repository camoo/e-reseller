<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ControllerException;
use App\Lib\DomainName;
use Camoo\Cache\Cache;
use CAMOO\Event\EventInterface;
use CAMOO\Exception\Exception;

/**
 * Class DomainsController
 *
 * @author CamooSarl
 */
class DomainsController extends AppController
{
    public ?\CAMOO\Controller\Component\SecurityComponent $Security = null;

    public ?\App\Model\Rest\DomainsRest $DomainsRest = null;

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
        $this->Security->setConfig('unlockedActions', ['domainSearch', 'addToBasket', 'removeFromBasket', 'isValid']);
    }

    public function domainSearch(): void
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            $status = false;
            $domain = DomainName::normalize((string)$this->request->getData('domain'));

            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);

            if (!empty($oNewRequest->getErrors())) {
                $this->showValidateErrors($oNewRequest);

                $this->_jsonResponse([
                    'status' => false,
                    'result' => $oNewRequest->getErrors(),
                ]);

                return;
            }

            if (($xRet = Cache::reads($domain, '_camoo_hosting_1hour')) === false) {
                $xRet = $oNewRequest->send(['::domains', 'checkAvailability'], false);
                Cache::writes($domain, $xRet, '_camoo_hosting_1hour');
            }

            if ($xRet) {
                $status = true;
            }

            $this->_jsonResponse([
                'status' => $status,
                'domain' => $domain,
            ]);

            return;
        }

        throw new Exception('Unknown error !');
    }

    public function overview(): void
    {
        $domain = $this->request->getQuery('d');
        if (empty($domain)) {
            $this->redirect('/');

            return;
        }

        $this->set('domain', $domain);
        $this->render();
    }

    public function addToBasket(): void
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
                $this->_jsonResponse(['status' => false, 'item' => []]);

                return;
            }

            $hDomain['basket_icon'] = 'flaticon-hosting';
            $hDomain['description'] = 'Nom de domaine';
            // other
            //$hDomain['basket_icon'] = 'flaticon-servers';
            $cart->addItem($domainBasket, $hDomain);
        }

        $this->_jsonResponse([
            'status' => $status,
            'item' => $hDomain,
        ]);
    }

    public function removeFromBasket(): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new ControllerException('Invalid request');
        }

        $cart = $this->getBasketRepository();
        $domain = strtolower((string)$this->request->getData('domain'));

        $cart->removeItem($domain);

        $this->_jsonResponse([
            'status' => true,
            'item' => $domain,
        ]);
    }

    public function decision(): void
    {
        $this->set('page_title', 'Indiquez un nom de domaine');
        $itemKeyId = $this->request->getQuery('kid');
        if (empty($itemKeyId)) {
            $this->redirect('/');

            return;
        }

        $this->set('item_key', $itemKeyId);
        $this->render();
    }

    public function isValid(): void
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

        $this->_jsonResponse([
            'status' => $status,
        ]);
    }
}
