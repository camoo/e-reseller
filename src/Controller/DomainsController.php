<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Cache\Cache;

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
    
            //$this->request->Flash->set('Error Message', ['alert' => 'error', 'key' => 'div_flash']);
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
        $domain = $this->request->getQuery('d');
        $this->set('domain', $domain);
        $this->render();
    }
}
