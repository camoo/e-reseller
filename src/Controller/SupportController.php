<?php

declare(strict_types=1);

namespace App\Controller;

use function Cake\I18n\__;
use Psr\Http\Message\ResponseInterface;

final class SupportController extends AppController
{
    public function overview(): ResponseInterface
    {
        return $this->render();
    }

    public function terms(): ResponseInterface
    {
        $this->set('page_title', __('Terms  and conditions'));

        return $this->render();
    }

    public function privacy(): ResponseInterface
    {
        $this->set('page_title', __('Privacy'));
        return $this->render();
    }
}
