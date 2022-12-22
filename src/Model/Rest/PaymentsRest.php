<?php

declare(strict_types=1);
namespace App\Model\Rest;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use Camoo\Hosting\Lib\Response;
use Camoo\Hosting\Modules\Payments;
use CAMOO\Interfaces\ValidationInterface;

class PaymentsRest extends AppRest
{
    public function initialized(): void
    {
        $this->loadRemoteObject('payments', new Payments());
    }

    public function validationDefault(ValidationInterface $validator): ValidationInterface
    {
        $validator
            ->requirePresence('phoneNumber', 'create')
            ->notEmptyString('phoneNumber', 'Le panier ne peut pas être vide');

        $validator
            ->decimal('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount', 'Indiquer un montant');

        $validator
            ->nonNegativeInteger('customer')
            ->requirePresence('customer', 'create')
            ->notEmptyString('customer', 'Indiquer l\'utilisateur ');

        return $validator;
    }

    /** @param Response $response */
    public function afterSend(Event $event, $response): void
    {
        if ($response->getStatusCode() !== 200 ||
            ($hResponse = $response->getJson()) && $hResponse['status'] === 'KO') {
            // dd($response);
            throw new Exception((string)$response->getError());
        }
        $this->output = $hResponse['result'];
    }
}
