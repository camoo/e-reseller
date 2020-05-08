<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Cache\Cache;
use CAMOO\Utils\Cart;
use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use CAMOO\Exception\Http\ForbiddenException;

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
        $this->Security->setConfig('unlockedActions', ['addToBasket', 'removeFromBasket', 'isValid']);
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
        if (empty($domain)) {
            return $this->redirect('/');
        }
        $this->set('domain', $domain);
        $this->render();
    }

    public function addToBasket()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            /** @var Cart **/
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
                    $hDomain['basket_icon'] = 'flaticon-hosting';
                    $hDomain['description'] = 'Nom de domaine';
                    // other
                    //$hDomain['basket_icon'] = 'flaticon-servers';
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
            /** @var Cart **/
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

    public function decision()
    {
        $this->set('page_title', 'Indiquez un nom de domaine');
        $this->render();
    }

    public function isValid()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            $status = false;
            $iUserId = (int) $this->request->getSession()->read('Auth.User.id');

            // CHECK TO ENSURE REFERRER URL IS ON THIS DOMAIN
            if (strpos($this->request->getEnv('HTTP_REFERER'), $this->request->getEnv('HTTP_HOST')) === false) {
                throw new ForbiddenException('Bad Referrer !');
            }

            $domain = $this->request->getData('domain');
            $asDomainCheck = explode('.', $domain);
            if (count($asDomainCheck) > 1) {
                $domain = count($asDomainCheck) > 1? array_shift($asDomainCheck) : $asDomainCheck[0];
            }

            $domain = strtolower($domain);

            $asInput = ['domain' => $domain, 'tlds' => implode(',', $this->allowedExtensions)];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);
    
            return $this->_jsonResponse([
                        'status' => empty($oNewRequest->getErrors()),
                        'result' => $oNewRequest->getErrors()
                    ]);
        }
        throw new Exception('Unknow error !');
    }
}
