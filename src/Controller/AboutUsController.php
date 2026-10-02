<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

final class AboutUsController extends AppController
{
    public function overview(): ResponseInterface
    {
        $this->set('page_title', $this->showcaseText('pages.about.title', 'À propos'));

        return $this->render();
    }
}
