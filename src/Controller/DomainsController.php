<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ControllerException;
use CAMOO\Cache\Cache;
use CAMOO\Event\Event;
use CAMOO\Exception\Exception;

/**
 * Class DomainsController
 *
 * @author CamooSarl
 */
class DomainsController extends AppController
{
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

    public function beforeAction(Event $event)
    {
        parent::beforeAction($event);
        $this->Security->setConfig('unlockedActions', ['domainSearch', 'addToBasket', 'removeFromBasket', 'isValid']);
    }

    public function domainSearch()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            $status = false;
            $domain = $this->request->getData('domain');
            $asDomainCheck = explode('.', $domain);
            if (count($asDomainCheck) > 1) {
                $domain = count($asDomainCheck) > 1 ? array_shift($asDomainCheck) : $asDomainCheck[0];
            }

            $domain = strtolower($domain);

            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);

            if (!empty($oNewRequest->getErrors())) {
                $this->showValidateErrors($oNewRequest);

                $this->_jsonResponse([
                    'status' => false,
                    'result' => $oNewRequest->getErrors(),
                ]);
            }

            if (($xRet = Cache::read($domain, '_camoo_hosting_1hour')) === false) {
                $xRet = $oNewRequest->send(['::domains', 'checkAvailability'], false);
                Cache::write($domain, $xRet, '_camoo_hosting_1hour');
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

    public function overview()
    {
        $domain = $this->request->getQuery('d');
        if (empty($domain)) {
            $this->redirect('/');

            return;
        }
        $this->set('domain', $domain);
        $this->render();
    }

    public function addToBasket()
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new ControllerException('Invalid Request type');
        }
        $oBasket = $this->getBasketRepository();
        $status = false;
        $domain = $domainBasket = strtolower($this->request->getData('domain'));
        $asDomainCheck = explode('.', $domain);
        if (count($asDomainCheck) > 1) {
            $domain = count($asDomainCheck) > 1 ? array_shift($asDomainCheck) : $asDomainCheck[0];
        }
        $xRet = Cache::read($domain, '_camoo_hosting_1hour');
        $hDomain = [];
        if ($xRet !== false && ($hDomain = $xRet[$domainBasket])) {
            $status = true;
            $hDomain['price'] = $hDomain['price']['addnewdomain'];
            $hDomain['basket_icon'] = 'flaticon-hosting';
            $hDomain['description'] = 'Nom de domaine';
            // other
            //$hDomain['basket_icon'] = 'flaticon-servers';
            $oBasket->addItem($domainBasket, $hDomain);
        }

        $this->_jsonResponse([
            'status' => $status,
            'item' => $hDomain,
        ]);
    }

    public function removeFromBasket()
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new ControllerException('Invalid request');
        }

        $oBasket = $this->getBasketRepository();
        $domain = $this->request->getData('domain');
        $domain = strtolower($domain);
        $oBasket->removeItem($domain);

        $this->_jsonResponse([
            'status' => true,
            'item' => $domain,
        ]);
    }

    public function decision()
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

    public function isValid()
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            throw new Exception('Unknown error !');
        }

        $domain = $this->request->getData('domain');

        $status = substr_count($domain, '.') > 0;
        if ($status === true) {
            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);
            $status = empty($oNewRequest->getErrors());
        }

        $this->_jsonResponse([
            'status' => $status,
        ]);
    }
}
