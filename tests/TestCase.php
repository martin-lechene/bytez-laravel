<?php

namespace Bytez\LaravelBytez\Tests;

use Bytez\LaravelBytez\BytezServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            BytezServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('bytez.api_key', 'test-api-key');
        $app['config']->set('bytez.base_url', 'https://api.bytez.com');
    }
}
