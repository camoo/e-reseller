<?php

declare(strict_types=1);

namespace App\Controller;

use function Cake\I18n\__;

final class PackagesController extends AppController
{
    public function overview(): void
    {
        $this->requireFeature('hosting');
        $this->set('page_title', __('Shared Web Hosting'));

        $this->render();
    }
}
