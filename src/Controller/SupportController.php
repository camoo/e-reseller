<?php

declare(strict_types=1);

namespace App\Controller;

use function Cake\I18n\__;
use Psr\Http\Message\ResponseInterface;

final class SupportController extends AppController
{
    public function overview(): ResponseInterface
    {
        $this->set('page_title', $this->showcaseText('pages.support.title', 'Support'));

        return $this->render();
    }

    public function terms(): ResponseInterface
    {
        $this->set('page_title', $this->showcaseText('pages.terms.title', __('Terms  and conditions')));

        return $this->render();
    }

    public function privacy(): ResponseInterface
    {
        $this->set('page_title', $this->showcaseText('pages.privacy.title', __('Privacy')));
        return $this->render();
    }
}
