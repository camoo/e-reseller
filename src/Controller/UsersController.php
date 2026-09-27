<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Rest\AppRest;
use Camoo\Cache\Cache;
use CAMOO\Exception\Exception;
use CAMOO\Utils\Cart;
use CAMOO\Utils\Configure;
use Psr\Http\Message\ResponseInterface;

/**
 * Class UsersController
 *
 * @author CamooSarl
 */
class UsersController extends AppController
{
    public ?\App\Model\Rest\UsersRest $UsersRest = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->loadRest('UsersRest');
    }

    public function join(): ResponseInterface
    {
        $this->request->allowMethod(['post', 'get']);
        if ($this->request->is('get')) {
            return $this->redirect('/#join');
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_ip'] = $this->request->getRemoteIp();
            $oNewRequest = $this->UsersRest->newRequest($data, true, ['action' => 'add']);

            if (empty($oNewRequest->getErrors()) && ($xRet = $oNewRequest->send(['::customers', 'add'])) && !empty($xRet['id'])) {
                $oNewRequest = $this->UsersRest->newRequest([$xRet['id']], false);
                if ($hUser = $oNewRequest->send(['::customers', 'getById'], false)) {
                    $this->doLogin($hUser);

                    return $this->redirect('/');
                }
            }

            if (!empty($oNewRequest->getErrors())) {
                $this->showValidateErrors($oNewRequest);
            }
        }

        return $this->redirect('/');
    }

    public function login(): ResponseInterface
    {
        $this->request->allowMethod(['post', 'get']);

        if ($this->request->is('get')) {
            return $this->redirect('/#login');
        }

        if ($this->request->is('post')) {
            if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
                return $this->redirect('/');
            }

            $data = [
                'email' => $this->request->getData('username'),
                'password' => $this->request->getData('passwd'),
            ];

            $oNewRequest = $this->UsersRest->newRequest($data, true, ['validation' => 'login']);
            if (empty($oNewRequest->getErrors()) && ($xRet = $oNewRequest->send(['::customers', 'auth']))) {
                $this->doLogin($xRet);

                return $this->redirect('/');
            }

            $this->request->Flash->error("Nom d'utilisateur ou mot de passe incorrect");
        }

        return $this->redirect('/');
    }

    public function logout(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
            $this->request->getSession()->delete('Auth');
            $this->request->getSession()->delete('loggedin');
            $this->request->getSession()->clear();
            $this->request->Flash->success('Déconnecté avec succès');
        }

        return $this->redirect('/');
    }

    public function getSSO(): ResponseInterface
    {
        $this->request->allowMethod(['get']);
        if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
            if ($this->request->is('ajax')) {
                $status = false;
                $ssoLink = null;
                $iUserId = $this->getUserId();

                $asId = [$iUserId];
                $oNewRequest = $this->UsersRest->newRequest($asId, false);
                if ($xRet = $oNewRequest->send(['::customers', 'getSsoToken'], false)) {
                    $status = true;
                    $ssoLink = 'https://' . Configure::read('RESELLER_SITE.domain') . '/rs/to-cp?token=' .
                        $xRet['sso_token'];
                }

                return $this->jsonResponse([
                    'status' => $status,
                    'sso_link' => $ssoLink,
                ]);

            }
        }

        throw new Exception('User not loggedIn !');
    }

    public function getBalance(): ResponseInterface
    {
        $this->request->allowMethod(['get']);
        if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
            $iUserId = $this->getUserId();
            $currency = $this->request->getQuery('currency');

            $params = [$iUserId];
            if (!empty($currency) && is_string($currency)) {
                $params[] = $currency;
            }

            $oNewRequest = $this->UsersRest->newRequest($params, false);
            $result = $oNewRequest->send(['::customers', 'getBalance'], false);

            return $this->jsonResponse([
                'status' => !empty($result),
                'balance' => $result,
            ]);
        }

        throw new Exception('User not loggedIn !');
    }

    public function editProfile(): ResponseInterface
    {
        $this->request->allowMethod(['post']);
        if ($this->request->getSession()->check('loggedin') && $this->request->getSession()->read('loggedin') === true) {
            $iUserId = $this->getUserId();
            $data = $this->request->getData();
            $data['id'] = $iUserId;

            $oNewRequest = $this->UsersRest->newRequest($data, true, ['validation' => 'edit', 'action' => 'edit']);
            if (empty($oNewRequest->getErrors())) {
                $response = $oNewRequest->send(['::customers', 'edit']);
                if ($this->request->is('ajax')) {
                    return $this->jsonResponse([
                        'status' => true,
                        'result' => $response,
                    ]);
                }
                $this->request->Flash->success('Profil mis à jour avec succès');

                return $this->redirect('/');
            }

            if ($this->request->is('ajax')) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $oNewRequest->getErrors(),
                ]);
            }

            $this->showValidateErrors($oNewRequest);

            return $this->redirect('/');
        }

        throw new Exception('User not loggedIn !');
    }

    private function doLogin(array $user): void
    {
        $basket = null;

        $this->request->getSession()->regenerateId();
        $this->request->getSession()->write('Auth.User', $user);
        $this->request->getSession()->write('loggedin', true);

        if (!empty($this->request->getSession()->check('Basket'))) {
            $basket = Cache::reads($this->request->getSession()->read('Basket'), '_camoo_hosting_conf');
            if ($basket instanceof Cart) {
                $basket->addRequest($this->request);
                Cache::deletes($this->request->getSession()->read('Basket'), '_camoo_hosting_conf');
            }
        }

        if ($basket instanceof Cart) {
            $basket->setUserId($user['id']);
            $basket->save();
        }

        $this->request->Flash->success('Connecté avec succès');
    }
}
