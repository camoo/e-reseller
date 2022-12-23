<?php

declare(strict_types=1);

namespace App\Console;

use Composer\IO\ConsoleIO;
use Composer\Script\Event;
use const DIRECTORY_SEPARATOR as DS;
use InvalidArgumentException;

final class Installer
{
    private const SALT_URL = 'https://api.wordpress.org/secret-key/1.1/salt/';

    public static function postInstall(Event $event): void
    {
        $consoleIO = $event->getIO();
        $rootDir = dirname(__DIR__, 2) . DS;
        self::createAppFileIfMissing($consoleIO, $rootDir);
        self::createDotEnvIfMissing($consoleIO, $rootDir);
        self::clearCaches($consoleIO, $rootDir);
    }

    private static function createAppFileIfMissing(ConsoleIO $consoleIO, string $rootDir): void
    {
        $configDir = $rootDir . 'config' . DS;
        $appFile = $configDir . 'app.php';

        if (is_file($appFile)) {
            return;
        }
        $distFile = $configDir . 'app.php.dist';
        $response = file_put_contents($appFile, file_get_contents($distFile));

        if ($response) {
            $consoleIO->write('File config/app.php successfully created!');

            return;
        }
        $consoleIO->write('Unable to create file config/app.php.');
    }

    private static function createDotEnvIfMissing(ConsoleIO $consoleIO, string $rootDir): void
    {
        $configDir = $rootDir . 'config' . DS;
        $envFile = $configDir . '.env';
        if (is_file($envFile)) {
            return;
        }
        $distFile = $configDir . '.env.dist';
        $content = file_get_contents($distFile);

        $salts = file_get_contents(self::SALT_URL);
        $saltsExploded = explode("\n", $salts);
        $saltRaw = current(array_filter($saltsExploded));

        $value = array_filter(
            explode("'", $saltRaw),
            fn (?string $val) => !empty($val) && strlen($val) > 32 ? $val : null
        );

        $salt = current($value);
        $find = ['__TOKEN_SALT_'];
        $replace = [$salt];
        if ($consoleIO->isInteractive()) {
            $username = $consoleIO->ask('Enter your camoo username: ');
            $password = $consoleIO->askAndHideAnswer('Enter your camoo password: ');
            if (empty($username) || empty($password)) {
                throw new InvalidArgumentException('Username or Password is missing!');
            }
            $find[] = '__USERNAME__';
            $find[] = '__PASSWORD__';
            $replace[] = $username;
            $replace[] = $password;
        }

        $content = str_replace($find, $replace, $content);

        $response = file_put_contents($envFile, $content);

        if ($response) {
            $consoleIO->write('File config/.env successfully created!');

            return;
        }
        $consoleIO->write('Unable to create file config/.env.');
    }

    private static function clearCaches(ConsoleIO $consoleIO, string $rootDir): void
    {
        if (!function_exists('shell_exec')) {
            return;
        }
        $command = sprintf('cd %s || exit && ./bin/camoo cleanup:all', $rootDir);
        $result = shell_exec($command);
        $consoleIO->write($result);
    }
}
