<?php

namespace RobertBoes\SidecarInertiaVite;

use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
use Hammerstone\Sidecar\Runtime;
use Hammerstone\Sidecar\Sidecar;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;

class SSRFunction extends LambdaFunction
{
    public function name()
    {
        return Config::get('sidecar-inertia-vite.name', 'Inertia-SSR-Vite');
    }

    public function runtime()
    {
        return Config::get('sidecar-inertia-vite.runtime', Runtime::NODEJS_16);
    }

    public function memory()
    {
        return Config::get('sidecar-inertia-vite.memory', 1024);
    }

    public function handler()
    {
        return $this->shouldBundle() ? 'index.handler' : 'ssr.handler';
    }

    public function package()
    {
        if ($this->shouldBundle()) {
            return Package::make()
                ->setBasePath(base_path('bootstrap/sidecar-ssr'))
                ->include([
                    '*',
                ]);
        }

        $package = Package::make()->setBasePath(base_path());

        $package->includeExactly([
            'node_modules' => 'node_modules',
            'bootstrap/ssr' => '',
        ]);

        return $package;
    }

    public function beforeDeployment()
    {
        Sidecar::log('Executing beforeDeployment hooks');

        // Compile the SSR bundle before deploying.
        $this->compileJavascript();

        $this->optimizeBundle();
    }

    protected function shouldBundle(): bool
    {
        return config('sidecar-inertia-vite.bundle', false);
    }

    protected function compileJavascript(): void
    {
        Sidecar::log('Build: Compiling Inertia SSR bundle.');

        $command = 'npx vite build --ssr';

        Sidecar::log("Build: Running \"{$command}\"");

        $process = new Process(explode(' ', $command), $cwd = base_path(), $env = []);

        // mustRun will throw an exception if it fails, which is what we want.
        $process->setTimeout(60)->disableOutput()->mustRun();

        Sidecar::log('Build: JavaScript SSR bundle compiled!');
    }

    protected function optimizeBundle(): void
    {
        if (! $this->shouldBundle()) {
            Sidecar::warning('Optimizing bundle: SKIPPED');

            return;
        }

        Sidecar::log('Optimizing bundle: Running NCC to compile SSR with node_modules bundled in');

        $command = 'npx --yes @vercel/ncc build bootstrap/ssr/ssr.mjs --out=bootstrap/sidecar-ssr';

        Sidecar::log("Optimizing bundle: Running \"{$command}\"");

        $process = new Process(explode(' ', $command), $cwd = base_path(), $env = []);

        // mustRun will throw an exception if it fails, which is what we want.
        $process->setTimeout(60)->disableOutput()->mustRun();

        Sidecar::log('Optimizing bundle: Package bundled with NCC!');
    }
}
