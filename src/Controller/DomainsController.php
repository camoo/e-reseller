<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Class DomainsController
 * @author CamooSarl
 */
class DomainsController extends AppController
{
    public function initialize() : void
    {
        /** @var Model\Rest\AppRest */
        $this->loadRest('DomainsRest');
    }

    private $allowedExtensions = 'cm,com,net,org,info,biz,site,pro,host';
    public function domainSearch()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('ajax')) {
            $status = false;
            $iUserId = (int) $this->request->getSession()->read('Auth.User.id');
			$this->request->Flash->error('Big Error');

            $domain = $this->request->getData('domain');
            $asDomainCheck = explode('.', $domain);
            if (count($asDomainCheck) > 1) {
                $domain = count($asDomainCheck) > 1? array_shift($asDomainCheck) : $asDomainCheck[0];
            }

            $asInput = ['domain' => $domain, 'tlds' => $this->allowedExtensions];
            $oNewRequest = $this->DomainsRest->newRequest($asInput, true, ['validation' => 'whois']);
    
			//$this->request->Flash->set('Big Error', ['alert' => 'error', 'key' => 'div_flash']);
			$this->request->Flash->error('Big Error');

            return $this->_jsonResponse([
                        'status'   => $status,
                        'result' => $oNewRequest->getErrors()
                    ]);
    
            if ($xRet = $oNewRequest->send(['::domains', 'checkAvailability'], false)) {
                $status = true;
            }

            return $this->_jsonResponse([
                        'status'   => $status,
                        'result' => $xRet
                    ]);
        }
        throw new Exception('Unknow error !');
    }
}
