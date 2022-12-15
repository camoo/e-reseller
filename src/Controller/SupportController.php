<?php

declare(strict_types=1);

namespace App\Controller;

final class SupportController extends AppController
{
    public function overview()
    {
        $this->render();
    }

    public function terms(): void
    {
        $this->set('page_title', __('Terms  and conditions'));

        $this->render();
    }

    public function privacy(): void
    {
        $this->set('page_title', __('Privacy'));
        $this->render();
    }
}
