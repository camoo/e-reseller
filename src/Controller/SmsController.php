<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Class SmsController
 *
 * @author yourname
 */
class SmsController extends AppController
{
    public function balance(): void
    {
        $response = ['balance' => 100, 'currency' => 'XAF'];
        $this->set('_serialize', $response);
    }
}
