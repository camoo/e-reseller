<?php

declare(strict_types=1);

namespace App\Model\Rest;

use ArrayObject;
use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use Camoo\Hosting\Lib\Response;
use Camoo\Hosting\Modules\Customers;
use CAMOO\Interfaces\ValidationInterface;

/**
 * Class UsersRest
 *
 * @author CamooSarl
 */
class UsersRest extends AppRest
{
    public function initialized(): void
    {
        /** @var Customers $customers */
        $this->loadRemoteObject('customers', new Customers());
    }

    public function validationDefault(ValidationInterface $validator): ValidationInterface
    {
        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmptyString('name', 'Entrer votre nom');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->requirePresence('password', 'create')
            ->add('password', 'custom', [
                'rule' => function ($sPassword) {
                    return (boolean)preg_match('/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z\\d]).*$/', $sPassword);
                },
                'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial',
            ])
            ->notEmptyString('password', 'Spécifiez votre mot de passe');

        $validator
            ->requirePresence('password_confirm', 'create')
            ->notEmptyString('password_confirm', 'Confirmez votre mot de passe')
            ->add('password_confirm', [
                'lengthBetween' => [
                    'rule' => ['lengthBetween', 8, 20],
                    'message' => sprintf('Votre mot de passe dot être compris entre %d et %d characters.', 8, 20),
                ],
                'equalToPassword' => [
                    'rule' => function ($value, $context) {
                        return (string)$value === (string)$context['data']['password'];
                    },
                    'message' => 'passwords_not_match',
                ],
            ]);

        $validator
            ->scalar('address')
            ->requirePresence('address', 'create')
            ->add('address', [
                'normal' => [
                    'rule' => function ($sAdr) {
                        // NOT email
                        if (mb_strpos($sAdr, '@') !== false) {
                            return false;
                        }
                        // NOT Number
                        //$sCcode = array_key_exists('ccode', $hConfig['data'])? $hConfig['data']['ccode'] : 'CM';
                        if (!empty((int)$sAdr)) {
                            return false;
                        }

                        return true;
                    },
                    'message' => 'Votre adresse semble être invalide',
                ],
            ])
            ->notEmptyString('address');

        $validator
            ->scalar('city')
            ->requirePresence('city', 'create')
            ->notEmptyString('city');

        $validator
            ->add('phone', 'valid', ['rule' => 'notBlank'])
            ->requirePresence('phone', 'create')
            ->notEmptyString('phone');

        $validator
            ->scalar('firstname')
            ->requirePresence('firstname', 'create')
            ->notEmptyString('firstname');

        return $validator;
    }

    public function validationLogin(ValidationInterface $validator): ValidationInterface
    {
        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'Mot de passe')
            ->add('password', [
                'lengthBetween' => [
                    'rule' => ['lengthBetween', 8, 20],
                    'message' => sprintf('Votre mot de passe dot être compris entre %d et %d characters.', 8, 20),
                ],
                'condition' => [
                    'rule' => function ($sPassword) {
                        return (boolean)preg_match('/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z\\d]).*$/', $sPassword);
                    },
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial',

                ],
            ]);

        return $validator;
    }

    public function beforeSend(Event $event, ArrayObject $option): void
    {
        $optionData = $option->getArrayCopy();
        if (!array_key_exists('action', $optionData) || $optionData['action'] !== 'add') {
            return;
        }

        $this->offsetSet('state', 'Centre');
        $this->offsetSet('company', 'N/A');
        $this->offsetSet('phone-cc', '237');
        $this->offsetSet('ccode', 'CM');
        $this->offsetSet('name', $this->get('firstname') . ' ' . $this->get('name'));
        $this->offsetSet('zipcode', '0000');
        $this->offsetSet('address-1', $this->get('address'));
        $this->offsetUnset('address');
        $this->offsetUnset('firstname');
    }

    /**
     * @param Response $response
     *                           return void
     */
    public function afterSend(Event $event, $response): void
    {
        if ($response->getStatusCode() !== 200 || ($hResponse = $response->getJson()) && $hResponse['status'] === 'KO') {
            throw new Exception((string)$response->getError());
        }
        $this->output = $hResponse['result'];
    }
}
