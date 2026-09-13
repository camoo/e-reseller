<?php

declare(strict_types=1);

namespace App\Command;

use CAMOO\Command\Command;

/**
 * Class HostingsCommand
 *
 * @author CamooSarl
 */
class HostingsCommand extends Command
{
    public function execute(): int
    {
        $this->out->success('Hostings command executed.');

        return Command::SUCCESS;
    }
}

