<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

final class AboutUsController extends AppController
{
    public function overview(): ResponseInterface
    {
        return $this->render();
    }
}
