<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Http\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use function Cake\I18n\__;

final class ErrorController extends AppController
{
    public function overview(): ResponseInterface
    {
        $this->controller = 'Error';

        return $this->render();
    }

    public function renderError(
        int $code = 404,
        ?string $message = null,
        ?ServerRequest $request = null,
    ): ResponseInterface {
        $this->request = $request ?? new ServerRequest();
        $this->controller = 'Error';
        $this->action = 'overview';
        $this->setResponse(new Response($code, ['Content-Type' => 'text/html; charset=UTF-8']));

        $this->wakeUpController();

        $defaultTitles = [
            400 => __('Requête incorrecte'),
            401 => __('Accès non autorisé'),
            403 => __('Accès interdit'),
            404 => __('Page introuvable'),
            405 => __('Méthode non autorisée'),
            429 => __('Trop de requêtes'),
            500 => __('Erreur interne du serveur'),
            502 => __('Passerelle incorrecte'),
            503 => __('Service temporairement indisponible'),
            504 => __('Délai d’attente dépassé'),
        ];

        $defaultMessages = [
            400 => __('La requête transmise est invalide ou ne peut pas être traitée.'),
            401 => __('Vous devez être authentifié pour accéder à cette ressource.'),
            403 => __('Vous n\'avez pas les autorisations nécessaires pour accéder à cette ressource.'),
            404 => __('La page ou la ressource que vous recherchez n\'existe pas ou a été déplacée.'),
            405 => __('La méthode HTTP utilisée n\'est pas autorisée pour cette ressource.'),
            429 => __('Vous avez envoyé trop de requêtes. Veuillez patienter avant de réessayer.'),
            500 => __('Une erreur inattendue est survenue sur notre serveur. Veuillez réessayer plus tard.'),
            502 => __('Le serveur a reçu une réponse non valide.'),
            503 => __('Le service est temporairement indisponible. Veuillez réessayer dans quelques instants.'),
            504 => __('Le serveur n\'a pas répondu à temps.'),
        ];

        $title = $defaultTitles[$code] ?? __('Une erreur est survenue');
        $desc = $message ?: ($defaultMessages[$code] ?? __('Une erreur inattendue s\'est produite.'));

        $this->set('code', $code);
        $this->set('title', $title);
        $this->set('message', $desc);
        $this->set('page_title', sprintf('%d - %s', $code, $title));

        return $this->render();
    }
}
