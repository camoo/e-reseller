<?php

declare(strict_types=1);

namespace App\Test;

use App\Application;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    public function testApplicationCanBeInstantiated(): void
    {
        $application = new Application();
        $this->assertInstanceOf(Application::class, $application);
    }
}
