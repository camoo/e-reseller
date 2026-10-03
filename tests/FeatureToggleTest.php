<?php

declare(strict_types=1);

namespace App\Test;

use App\Controller\AppController;
use App\Template\Extension\Functions\Lib;
use CAMOO\Utils\Configure;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FeatureToggleTest extends TestCase
{
    private string|false $savedFeatureDomains;
    private string|false $savedFeatureHosting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedFeatureDomains = getenv('FEATURE_DOMAINS');
        $this->savedFeatureHosting = getenv('FEATURE_HOSTING');
    }

    protected function tearDown(): void
    {
        if ($this->savedFeatureDomains === false) {
            putenv('FEATURE_DOMAINS');
        } else {
            putenv('FEATURE_DOMAINS=' . $this->savedFeatureDomains);
        }

        if ($this->savedFeatureHosting === false) {
            putenv('FEATURE_HOSTING');
        } else {
            putenv('FEATURE_HOSTING=' . $this->savedFeatureHosting);
        }

        parent::tearDown();
    }

    private function createLib(): Lib
    {
        return (new ReflectionClass(Lib::class))->newInstanceWithoutConstructor();
    }

    public function testLibFeatureEnabledReadsConfiguredFeatures(): void
    {
        Configure::write('Features', ['domains' => true, 'hosting' => false]);

        $lib = $this->createLib();
        self::assertTrue($lib->featureEnabled('domains'));
        self::assertFalse($lib->featureEnabled('hosting'));
    }

    public function testLibFeatureEnabledFallsBackToEnvironmentWhenConfigMissing(): void
    {
        Configure::write('Features', null);
        putenv('FEATURE_DOMAINS=true');
        putenv('FEATURE_HOSTING=false');

        $lib = $this->createLib();
        self::assertTrue($lib->featureEnabled('domains'));
        self::assertFalse($lib->featureEnabled('hosting'));
    }

    public function testLibFeatureEnabledDefaultsToTrueWhenUnset(): void
    {
        Configure::write('Features', null);
        putenv('FEATURE_DOMAINS');

        $lib = $this->createLib();
        self::assertTrue($lib->featureEnabled('domains'));
    }

    public function testAppControllerFeatureEnabledMatchesLibBehavior(): void
    {
        Configure::write('Features', null);
        putenv('FEATURE_DOMAINS=true');
        putenv('FEATURE_HOSTING=false');

        $controller = new class extends AppController {
            public function testIsFeatureEnabled(string $feature): bool
            {
                return $this->isFeatureEnabled($feature);
            }
        };

        self::assertTrue($controller->testIsFeatureEnabled('domains'));
        self::assertFalse($controller->testIsFeatureEnabled('hosting'));
    }
}
