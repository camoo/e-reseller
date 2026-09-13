<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Class SmsController
 *
 * @author yourname
 */
class SmsController extends AppController
{
    public function balance(): ResponseInterface
    {
        $response = ['balance' => 100, 'currency' => 'XAF'];

        return $this->jsonResponse($response);
    }
}
