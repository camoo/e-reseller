<?php

declare(strict_types=1);

namespace App\Controller;

use function Cake\I18n\__;
use Psr\Http\Message\ResponseInterface;

final class PackagesController extends AppController
{
    public function overview(): ResponseInterface
    {
        $this->requireFeature('hosting');
        $this->set('page_title', $this->showcaseText('pages.packages.title', __('Shared Web Hosting')));

        return $this->render();
    }

    public function vps(): ResponseInterface
    {
        $this->requireFeature('servers');
        $this->set('page_title', $this->showcaseText('pages.vps.title', __('VPS')));
        $this->set('product_type', 'vps');

        return $this->render();
    }

    public function email(): ResponseInterface
    {
        $this->requireFeature('emails');
        $this->set('page_title', $this->showcaseText('pages.emails.title', __('Professional Email')));
        $this->set('product_type', 'emails');

        return $this->render();
    }

    public function ssl(): ResponseInterface
    {
        $this->requireFeature('ssl');
        $this->set('page_title', $this->showcaseText('pages.ssl.title', __('SSL Certificates')));
        $this->set('product_type', 'ssl');

        return $this->render();
    }
}
