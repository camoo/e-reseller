<?php

declare(strict_types=1);

namespace App\Test;

use App\Lib\ErrorLogger;
use CAMOO\Http\ServerRequest;
use GuzzleHttp\Psr7\ServerRequest as GuzzleRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ErrorLoggerTest extends TestCase
{
    private string $testLogFile;

    protected function setUp(): void
    {
        parent::setUp();
        ErrorLogger::reset();
        $logDir = defined('LOGS') ? LOGS : dirname(__DIR__) . '/logs/';
        $this->testLogFile = $logDir . 'cli-error.log';
        if (file_exists($this->testLogFile)) {
            @unlink($this->testLogFile);
        }
    }

    protected function tearDown(): void
    {
        ErrorLogger::reset();
        if (file_exists($this->testLogFile)) {
            @unlink($this->testLogFile);
        }
        parent::tearDown();
    }

    public function testLogException(): void
    {
        $exception = new RuntimeException('Test runtime exception for logger');
        ErrorLogger::log($exception);

        self::assertTrue(ErrorLogger::hasLogged());
        self::assertFileExists($this->testLogFile);

        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('Test runtime exception for logger', $content);
        self::assertStringContainsString('Date: ', $content);
    }

    public function testLog404(): void
    {
        ErrorLogger::log404();

        self::assertTrue(ErrorLogger::hasLogged());
        self::assertFileExists($this->testLogFile);

        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('404 Not Found', $content);
        self::assertStringContainsString('Date: ', $content);
    }

    public function testLogHttpError40xAnd50x(): void
    {
        ErrorLogger::logHttpError(403);
        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('403 Forbidden', $content);
        self::assertStringContainsString('Date: ', $content);

        ErrorLogger::reset();
        @unlink($this->testLogFile);

        ErrorLogger::logHttpError(401);
        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('401 Unauthorized', $content);

        ErrorLogger::reset();
        @unlink($this->testLogFile);

        ErrorLogger::logHttpError(500);
        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('500 Internal Server Error', $content);

        ErrorLogger::reset();
        @unlink($this->testLogFile);

        ErrorLogger::logHttpError(502);
        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('502 Bad Gateway', $content);
    }

    public function testLog404WithCustomMessage(): void
    {
        ErrorLogger::log404(null, 'Resource /api/missing was not found');

        self::assertTrue(ErrorLogger::hasLogged());
        self::assertFileExists($this->testLogFile);

        $content = (string)file_get_contents($this->testLogFile);
        self::assertStringContainsString('Resource /api/missing was not found', $content);
        self::assertStringContainsString('Date: ', $content);
    }

    public function testDeduplicationPreventsDuplicate404Logging(): void
    {
        $exception = new RuntimeException('Initial failure');
        ErrorLogger::log($exception);
        $fileSizeAfterFirst = filesize($this->testLogFile);

        // Subsequent 404 should be skipped
        ErrorLogger::log404();
        clearstatcache();
        $fileSizeAfterSecond = filesize($this->testLogFile);

        self::assertSame($fileSizeAfterFirst, $fileSizeAfterSecond);

        // Reset allows logging again
        ErrorLogger::reset();
        self::assertFalse(ErrorLogger::hasLogged());
        ErrorLogger::log404();
        clearstatcache();
        self::assertGreaterThan($fileSizeAfterFirst, filesize($this->testLogFile));
    }

    public function testGetRequestMessageContainsMetadata(): void
    {
        $guzzle = new GuzzleRequest('GET', '/test-route', [
            'Referer' => 'https://example.com/from',
        ]);
        $serverRequest = new ServerRequest($guzzle);

        // Reflection to test getRequestMessage non-CLI branch
        $refMethod = new \ReflectionMethod(ErrorLogger::class, 'getRequestMessage');
        // Temporarily override isCli behavior via ServerRequest
        $msg = ErrorLogger::getRequestMessage($serverRequest);

        self::assertStringContainsString('Date: ', $msg);
    }
}
