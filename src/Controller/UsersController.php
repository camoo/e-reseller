<?php
declare(strict_types=1);

namespace App\Controller;

use CAMOO\Exception\Exception;
use CAMOO\Utils\Configure;
use CAMOO\Cache\Cache;
use App\Lib\Utils\Basket;

/**
 * Class UsersController
 * @author CamooSarl
 */
class UsersController extends AppController
{
    public function initialize() : void
    {
        /** @var Model\Rest\AppRest */
        $this->loadRest('UsersRest');
    }

    public function join()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();
        $data['user_ip'] = $this->request->getRemoteIp();
        $oNewRequest = $this->UsersRest->newRequest($data, true, ['action' => 'add']);

        if (empty($oNewRequest->getErrors()) && ($xRet = $oNewRequest->send(['::customers', 'add']))) {
            if (!empty($xRet['id'])) {
                $oNewRequest = $this->UsersRest->newRequest([$xRet['id']], false);
                if ($hUser = $oNewRequest->send(['::customers', 'getById'], false)) {
                    $this->doLogin($hUser);
                    return $this->redirect('/');
                }
            }
        }
        throw new Exception('Error !');
    }

    public function login()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('post')) {
            if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
                return $this->redirect('/');
            }
            $data = [
                'email'    => $this->request->getData('username'),
                'password' => $this->request->getData('passwd'),
            ];
            $oNewRequest = $this->UsersRest->newRequest($data, true, ['validation' => 'login']);
            if (empty($oNewRequest->getErrors()) && ($xRet = $oNewRequest->send(['::customers', 'auth']))) {
                $this->doLogin($xRet);
                return $this->redirect('/');
            }
            throw new Exception('Error !');
        }
    }

    public function logout()
    {
        $this->request->allowMethod(['get']);
        if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
            $this->request->getSession()->delete('Auth');
            $this->request->getSession()->delete('loggedin');
            $this->request->getSession()->clear();

            return $this->redirect('/');
        }
        throw new Exception('User not loggedIn !');
    }

    private function doLogin(array $user) : void
    {
        /** @var Basket */
        $basket = null;

        if (!empty($this->request->getSession()->check('Basket'))) {
            $basket = Cache::read($this->request->getSession()->read('Basket'));
            if ($basket instanceof Basket) {
                Cache::delete($this->request->getSession()->read('Basket'), '_camoo_hosting_conf');
            }
        }

        $this->request->getSession()->write('Auth.User', $user);
        $this->request->getSession()->write('loggedin', true);

        if (null !== $basket && $basket instanceof Basket) {
            $Object->save();
        }
    }

    public function getSSO()
    {
        $this->request->allowMethod(['get']);
        if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
            if ($this->request->is('ajax')) {
                $status = false;
                $ssoLink = null;
                $iUserId = (int) $this->request->getSession()->read('Auth.User.id');

                $asId = [$iUserId];
                $oNewRequest = $this->UsersRest->newRequest($asId, false);
                if ($xRet = $oNewRequest->send(['::customers', 'getSsoToken'], false)) {
                    $status = true;
                    $ssoLink = 'https://'. Configure::read('RESELLER_SITE.domain'). '/rs/to-cp?token='. $xRet['sso_token'];
                }

                return $this->_jsonResponse([
                        'status'   => $status,
                        'sso_link' => $ssoLink
                    ]);
            }
        }
        throw new Exception('User not loggedIn !');
    }
}
