<?php

declare(strict_types=1);

namespace App\Model\Rest;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use Camoo\Hosting\Lib\Response;
use Camoo\Hosting\Modules\Contacts;
use CAMOO\Interfaces\ValidationInterface;

/**
 * Class ContactsRest
 *
 * @author CamooSarl
 */
class ContactsRest extends AppRest
{
    public function initialized(): void
    {
        $this->loadRemoteObject('contacts', new Contacts());
    }

    public function validationDefault(ValidationInterface $validator): ValidationInterface
    {
        return $this->validationEdit($validator);
    }

    public function validationEdit(ValidationInterface $validator): ValidationInterface
    {
        $validator
            ->integer('id')
            ->requirePresence('id', 'create')
            ->notEmptyString('id');

        $validator
            ->scalar('name')
            ->allowEmptyString('name');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('phone')
            ->allowEmptyString('phone');

        return $validator;
    }

    /**
     * @param Response $response
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
