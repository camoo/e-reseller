<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Cache\Cache;
use App\Lib\Utils\Basket;
use CAMOO\Event\Event;

/**
 * Class DomainsController
 * @author CamooSarl
 */
class DomainsController extends AppController
{
    public function initialize() : void
    {
        /** @var Model\Rest\AppRest */
        parent::initialize();
        $this->loadRest('DomainsRest');
    }

    public function beforeAction(Event $event)
    {
        parent::beforeAction($event);
        $this->Security->setConfig('unlockedActions', ['addToBasket', 'removeFromBasket']);
    }

    /** @var array $allowedExtensions */
    private $allowedExtensions = [
        'cm',
        'com',
        'net',
        'org',
        'info',
        'biz',
        'site',
        'pro',
        'host'
    ];

    public function domainSearch()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            $status = false;
            $iUserId = (int) $this->request->getSession()->read('Auth.User.id');

            $domain = $this->request->getData('domain');
            $asDomainCheck = explode('.', $domain);
            if (count($asDomainCheck) > 1) {
                $domain = count($asDomainCheck) > 1? array_shift($asDomainCheck) : $asDomainCheck[0];
            }

            $domain = strtolower($domain);

            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);
    
            if (!empty($oNewRequest->getErrors())) {
                $this->showValidateErrors($oNewRequest);
                    
                return $this->_jsonResponse([
                        'status' => $status,
                        'result' => $oNewRequest->getErrors()
                    ]);
            }
   
            if (($xRet = Cache::read($domain, '_camoo_hosting_1hour')) === false) {
                $xRet = $oNewRequest->send(['::domains', 'checkAvailability'], false);
                Cache::write($domain, $xRet, '_camoo_hosting_1hour');
            }

            if ($xRet) {
                $status = true;
            }

            return $this->_jsonResponse([
                        'status' => $status,
                        'domain' => $domain
                    ]);
        }
        throw new Exception('Unknow error !');
    }

    public function overview()
    {
        //debug($this->Security);
        $domain = $this->request->getQuery('d');
        $this->set('domain', $domain);
        $this->render();
    }

    public function addToBasket()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            /** @var Basket **/
            $oBasket = $this->getBasketRepository();
            $status = false;
            $iUserId = (int) $this->request->getSession()->read('Auth.User.id');
            $domain = $domainBasket = strtolower($this->request->getData('domain'));
            $asDomainCheck = explode('.', $domain);
            if (count($asDomainCheck) > 1) {
                $domain = count($asDomainCheck) > 1? array_shift($asDomainCheck) : $asDomainCheck[0];
            }

            if (($xRet = Cache::read($domain, '_camoo_hosting_1hour')) !== false) {
                if ($hDomain = $xRet[$domainBasket]) {
                    $status = true;
                    $hDomain['price'] = $hDomain['price']['addnewdomain'];
                    $oBasket->addItem($domainBasket, $hDomain);
                }
            }

            return $this->_jsonResponse([
                        'status' => $status,
                        'item' => $hDomain
                    ]);
        }
    }

    public function removeFromBasket()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            /** @var Basket **/
            $oBasket = $this->getBasketRepository();
            $status = true;
            $domain = $this->request->getData('domain');
            $domain = strtolower($domain);
            $oBasket->removeItem($domain);

            return $this->_jsonResponse([
                        'status' => $status,
                        'item' => $domain
                    ]);
        }
    }
}
