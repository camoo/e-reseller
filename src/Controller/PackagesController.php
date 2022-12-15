<?php

declare(strict_types=1);

namespace App\Controller;

final class PackagesController extends AppController
{
    public function overview()
    {
        $this->set('page_title', __('Shared Web Hosting'));

        $this->render();
    }
}
