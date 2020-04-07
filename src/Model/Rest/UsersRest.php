<?php
declare(strict_types=1);

namespace App\Model\Rest;

use CAMOO\Model\Rest\AppRest as BaseRest;
use CAMOO\Interfaces\ValidationInterface;
use CAMOO\Event\Event;
use Camoo\Hosting\Modules\Customers;
use Camoo\Hosting\Lib\Response;
use CAMOO\Exception\Exception;

/**
 * Class UsersRest
 * @author CamooSarl
 */
class UsersRest extends BaseRest
{
    public function initialized() : void
    {
        /** @var Customers $customers */
        $this->loadRemoteObject('customers', new Customers);
    }

    public function validationDefault(ValidationInterface $validator) : ValidationInterface
    {
        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmpty('name', 'Entrer votre nom');

        $validator
            ->email('email')
                ->requirePresence('email', 'create')
                ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->add('password', 'custom', [
                'rule' => function ($sPassword, $context) {
                    return (boolean)preg_match('/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z\\d]).*$/', $sPassword);
                },
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial',
                ])

            ->notEmpty('password', 'Spécifiez votre mot de passe');

        $validator
            ->requirePresence('password_confirm', 'create')
            ->notEmpty('password_confirm', 'Confirmez votre mot de passe')
            ->add('password_confirm', [
                'lengthBetween' => [
                    'rule' => ['lengthBetween', 8, 20],
                    'message' => sprintf('Votre mot de passe dot être compris entre %d et %d characters.', 8, 20)
                ],
                'equalToPassword' => [
                    'rule' => function ($value, $context) {
                        return (string) $value === (string) $context['data']['password'];
                    },
                        'message' => 'passwords_not_match'
                    ]
                ]);

        $validator
            ->scalar('address')
            ->requirePresence('address', 'create')
            ->add('address', [
                'normal' => [
                    'rule' => function ($sAdr, $hConfig) {
                        // NOT email
                        if (mb_strpos($sAdr, '@') !== false) {
                            return false;
                        }
                        // NOT Number
                        //$sCcode = array_key_exists('ccode', $hConfig['data'])? $hConfig['data']['ccode'] : 'CM';
                        if (!empty((int) $sAdr)) {
                            return false;
                        }

                        return true;
                    },
                        'message' => 'Votre adresse semble être invalide'
                    ]
                ])
            ->notEmpty('address');

        $validator
            ->scalar('city')
            ->requirePresence('city', 'create')
            ->notEmpty('city');

        $validator
            ->add('phone', 'valid', ['rule' => 'notBlank'])
            ->requirePresence('phone', 'create')
            ->notEmpty('phone');

        $validator
            ->scalar('firstname')
            ->requirePresence('firstname', 'create')
            ->notEmpty('firstname');

        return $validator;
    }

    public function validationLogin(ValidationInterface $validator) : ValidationInterface
    {
        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->notEmpty('password', 'Mot de passe')
            ->add('password', [
                'lengthBetween' => [
                    'rule' => ['lengthBetween', 8, 20],
                    'message' => sprintf('Votre mot de passe dot être compris entre %d et %d characters.', 8, 20)
                ],
                'condition' => [
                'rule' => function ($sPassword, $context) {
                    return (boolean)preg_match('/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z\\d]).*$/', $sPassword);
                },
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial',

                ]
                ]);

        return $validator;
    }

    public function beforeSend(Event $event, \ArrayObject $data) : void
    {
        if (!empty($data) && array_key_exists('action', $data) && $data['action'] === 'add') {
            $this->offsetSet('state', 'Centre');
            $this->offsetSet('company', 'N/A');
            $this->offsetSet('phone-cc', '237');
            $this->offsetSet('ccode', 'CM');
            $this->offsetSet('name', $this->get('firstname'). ' ' . $this->get('name'));
            $this->offsetSet('zipcode', '0000');
            $this->offsetSet('address-1', $this->get('address'));
            $this->offsetUnset('address');
            $this->offsetUnset('firstname');
        }
    }

    /**
     * @param Event $event
     * @param Response $response
     * return void
     */
    public function afterSend(Event $event, $response) : void
    {
        if ($response->getStatusCode() !== 200 || ($hResponse = $response->getJson()) && $hResponse['status'] === 'KO') {
            throw new Exception((string)$response->getError());
        }
        $this->output = $hResponse['result'];
    }
}
