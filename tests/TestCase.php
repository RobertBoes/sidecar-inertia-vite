<?php

namespace RobertBoes\SidecarInertiaVite\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RobertBoes\SidecarInertiaVite\ServiceProvider as SidecarInertiaViteProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SidecarInertiaViteProvider::class,
        ];
    }

}
