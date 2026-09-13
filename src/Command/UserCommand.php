<?php

declare(strict_types=1);

namespace App\Command;

use CAMOO\Command\Command;
use CAMOO\Console\Input\InputArgument;
use Camoo\Http\Curl\Domain\Client\ClientInterface;
use Camoo\Inflector\Inflector;

/**
 * Class UserCommand
 *
 * @author CamooSarl
 */
class UserCommand extends Command
{
    public function __construct()
    {
    }

    public function execute(): int
    {
        #$this->out->info('command starts');
        $name = 'Top_Hosting';

        $result = implode(' ', array_map([Inflector::class, 'classify'], explode('_', $name)));
        $hu = Inflector::humanize($name);
        debug($result, $hu);
        $this->out->success('End');

        return Command::SUCCESS;
    }

    public function add(int $uid): int
    {
        dd($uid);
        $this->out->success('Done');

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('add', InputArgument::OPTIONAL)
            ->addArgument('add_args1', InputArgument::OPTIONAL)
        ;
    }
}
