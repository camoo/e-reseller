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
                'rule' => fn($sPassword): bool => (boolean)preg_match('/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z\\d]).*$/', $sPassword),
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
                    'rule' => fn($value, $context): bool => (string)$value === (string)$context['data']['password'],
                    'message' => 'passwords_not_match',
                ],
            ]);

        $validator
            ->scalar('address')
            ->requirePresence('address', 'create')
            ->add('address', [
                'normal' => [
                    'rule' => function ($sAdr): bool {
                        // NOT email
                        if (str_contains($sAdr, '@')) {
                            return false;
                        }

                        // NOT Number
                        //$sCcode = array_key_exists('ccode', $hConfig['data'])? $hConfig['data']['ccode'] : 'CM';
                        return empty((int)$sAdr);
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

    public function validationLogin(ValidationInterface $validation): ValidationInterface
    {
        $validation
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validation
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'Mot de passe')
            ->add('password', [
                'lengthBetween' => [
                    'rule' => ['lengthBetween', 8, 20],
                    'message' => sprintf('Votre mot de passe dot être compris entre %d et %d characters.', 8, 20),
                ],
                'condition' => [
                    'rule' => fn($sPassword): bool => (boolean)preg_match('/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z\\d]).*$/', $sPassword),
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial',

                ],
            ]);

        return $validation;
    }

    public function validationEdit(ValidationInterface $validation): ValidationInterface
    {
        $validation
            ->integer('id')
            ->requirePresence('id', 'create')
            ->notEmptyString('id');

        $validation
            ->scalar('name')
            ->allowEmptyString('name');

        $validation
            ->email('email')
            ->allowEmptyString('email');

        $validation
            ->scalar('address')
            ->allowEmptyString('address');

        $validation
            ->scalar('city')
            ->allowEmptyString('city');

        $validation
            ->scalar('phone')
            ->allowEmptyString('phone');

        return $validation;
    }

    public function beforeSend(Event $event, ArrayObject $option): void
    {
        $optionData = $option->getArrayCopy();
        $action = $optionData['action'] ?? null;

        if ($action === 'add') {
            $this->offsetSet('state', 'Centre');
            $this->offsetSet('company', 'N/A');
            $this->offsetSet('phone-cc', '237');
            $this->offsetSet('ccode', 'CM');
            $this->offsetSet('name', $this->get('firstname') . ' ' . $this->get('name'));
            $this->offsetSet('zipcode', '0000');
            $this->offsetSet('address-1', $this->get('address'));
            $this->offsetUnset('address');
            $this->offsetUnset('firstname');
            return;
        }

        if ($action === 'edit') {
            if ($this->has('firstname') && $this->has('name')) {
                $this->offsetSet('name', $this->get('firstname') . ' ' . $this->get('name'));
                $this->offsetUnset('firstname');
            }
            if ($this->has('address')) {
                $this->offsetSet('address-1', $this->get('address'));
                $this->offsetUnset('address');
            }
        }
    }

    /**
     * @param Response $response
     *                           return void
     */
    public function afterSend(Event $event, $response): void
    {
        if ($response->getStatusCode() !== 200 ||
            (($hResponse = $response->getJson()) && ($hResponse['status'] ?? '') === 'KO')) {
            throw new Exception((string)$response->getError());
        }

        $this->output = $hResponse['result'] ?? $hResponse;
    }
}
