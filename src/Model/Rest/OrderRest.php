<?php

namespace App\Model\Rest;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use Camoo\Hosting\Lib\Response;
use Camoo\Hosting\Modules\Orders;
use CAMOO\Interfaces\ValidationInterface;

class OrderRest extends AppRest
{
    public function initialized(): void
    {
        $this->loadRemoteObject('orders', new Orders());
    }

    public function validationDefault(ValidationInterface $validator): ValidationInterface
    {
        $validator
            ->requirePresence('body', 'create')
            ->notEmptyString('body', 'Le panier ne peut pas être vide')
            ->add('body', [
                'condition' => [
                    'rule' => fn (string $body) => json_decode($body) && json_last_error() === JSON_ERROR_NONE,
                    'message' => 'Commande invalide',

                ],
            ]);

        return $validator;
    }

    /**
     * @param Response $response
     *                           return void
     */
    public function afterSend(Event $event, $response): void
    {
        if ($response->getStatusCode() !== 200 ||
            ($hResponse = $response->getJson()) && $hResponse['status'] === 'KO') {
            throw new Exception((string)$response->getError());
        }
        $this->output = $hResponse['result'];
    }
}
