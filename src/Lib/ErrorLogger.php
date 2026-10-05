<?php

declare(strict_types=1);

namespace App\Lib;

use CAMOO\Http\ServerRequest;
use CAMOO\Utils\Utility;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Throwable;

final class ErrorLogger
{
    private static bool $hasLogged = false;

    public static function log(Throwable|string $error, ?ServerRequest $request = null): void
    {
        self::$hasLogged = true;

        $logName = self::isCli() ? 'cli-error.log' : 'error.log';
        $logDir = defined('LOGS') ? LOGS : dirname(__DIR__, 2) . '/logs/';
        $logFile = $logDir . $logName;

        if ($error instanceof Throwable) {
            $entry = $error->getMessage() . "\n" . $error->getTraceAsString();
        } else {
            $entry = trim($error);
        }

        $entry .= self::getRequestMessage($request);

        @error_log($entry, 3, $logFile);
    }

    private const HTTP_STATUS_PHRASES = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
    ];

    public static function logHttpError(int $statusCode, ?ServerRequest $request = null, ?string $message = null): void
    {
        if (self::$hasLogged) {
            return;
        }

        $phrase = self::HTTP_STATUS_PHRASES[$statusCode] ?? ($statusCode >= 500 ? 'Server Error' : 'Client Error');
        $text = $message ?: sprintf('%d %s', $statusCode, $phrase);

        self::log($text, $request);
    }

    public static function log404(?ServerRequest $request = null, ?string $message = null): void
    {
        self::logHttpError(404, $request, $message);
    }

    public static function hasLogged(): bool
    {
        return self::$hasLogged;
    }

    public static function reset(): void
    {
        self::$hasLogged = false;
    }

    public static function getRequestMessage(?ServerRequest $request = null): string
    {
        $date = date('Y-m-d H:i:s');

        if (self::isCli()) {
            return "\nDate: " . $date . "\n";
        }

        $requestTarget = null;
        $referer = null;
        $clientIp = null;

        if ($request !== null) {
            $requestTarget = $request->getRequestTarget();
            $referer = $request->getReferer();
            $clientIp = $request->getRemoteIp();
        } else {
            try {
                $serverRequest = new ServerRequest(GuzzleServerRequest::fromGlobals());
                $requestTarget = $serverRequest->getRequestTarget();
                $referer = $serverRequest->getReferer();
                $clientIp = $serverRequest->getRemoteIp();
            } catch (\Throwable) {
                $requestTarget = $_SERVER['REQUEST_URI'] ?? '/';
                $referer = $_SERVER['HTTP_REFERER'] ?? null;
                $clientIp = $_SERVER['REMOTE_ADDR'] ?? null;
            }
        }

        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
            $clientIp = (string)$_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        $message = "\nRequest URL: " . ($requestTarget ?: '/');
        if (!empty($referer)) {
            $message .= "\nReferer URL: " . $referer;
        }
        if (!empty($clientIp) && $clientIp !== '::1') {
            $message .= "\nClient IP: " . $clientIp;
        }
        $message .= "\nDate: " . $date . "\n";

        return $message;
    }

    private static function isCli(): bool
    {
        return Utility::isCli();
    }
}
