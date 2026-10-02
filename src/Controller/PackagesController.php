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
}
