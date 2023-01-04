<?php

declare(strict_types=1);

namespace App\Di\Module;

use Camoo\Http\Curl\Domain\Client\ClientInterface;
use Camoo\Http\Curl\Infrastructure\Client;
use Ray\Di\AbstractModule;

final class HttpModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(ClientInterface::class)->to(Client::class);
    }
}
