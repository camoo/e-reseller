<?php

declare(strict_types=1);

namespace App\Model\Rest;

use CAMOO\Event\Event;
use CAMOO\Exception\Exception;
use Camoo\Hosting\Lib\Response;
use Camoo\Hosting\Modules\Domains;
use CAMOO\Interfaces\ValidationInterface;

/**
 * Class DomainsRest
 *
 * @author CamooSarl
 */
class DomainsRest extends AppRest
{
    public function initialized(): void
    {
        /** @var Domains $domains */
        $this->loadRemoteObject('domains', new Domains());
    }

    public function validationDefault(ValidationInterface $validator): ValidationInterface
    {
        return $validator;
    }

    public function validationWhois(ValidationInterface $validator): ValidationInterface
    {
        $validator
            ->requirePresence('domain', 'create')
            ->notEmptyString('domains', 'Le nom de domaine')
            ->add('domain', [
                'condition' => [
                    'rule' => function (string $domain) {
                        if (substr_count($domain, '.') > 0) {
                            return (boolean)preg_match(
                                '/^(?:[a-zA-Z0-9]+(?:-*[a-zA-Z0-9])*\.)+[a-zA-Z]{2,}$/',
                                $domain
                            );
                        }

                        return (boolean)preg_match('/\A(?!-)[a-zA-Z0-9-]{2,}(?<!-)\z/i', $domain);
                    },
                    'message' => 'nom de domaine invalide',

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
