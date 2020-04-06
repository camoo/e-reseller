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
                ]);

        return $validator;
    }

    public function beforeSend(Event $event, \ArrayObject $data) : void
    {
    }

    /**
     * @param Event $event
     * @param Response $response
     * return void
     */
    public function afterSend(Event $event, $response) : void
    {
        if ($response->getStatusCode() !== 200 || ($hResponse = $response->getJson()) && $hResponse['status'] === 'KO') {
            throw new Exception($response->getError());
        }
        $this->output = $hResponse['result'];
    }
}
