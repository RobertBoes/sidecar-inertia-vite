# Sidecar SSR for InertiaJS with Vite

> 🚨 Use this package with caution, as I'm still working on it

This package is based on [hammerstonedev/sidecar-inertia](https://github.com/hammerstonedev/sidecar-inertia).
Aaron has done an amazing job with Sidecar and his original package lead me to figure out how to make it work with Vite.

## Overview 

This package provides a Sidecar function to run [Inertia server-side rendering](https://inertiajs.com/server-side-rendering) on AWS Lambda.

Sidecar packages, deploys, and executes AWS Lambda functions from your Laravel application.

- [Sidecar docs](https://hammerstone.dev/sidecar/docs/main/overview)
- [Sidecar GitHub](https://github.com/hammerstonedev/sidecar)

## Enabling SSR

Following the [official Inertia docs](https://inertiajs.com/server-side-rendering#enabling-ssr) on enabling SSR is a good place to start, but there are a few things you can skip:
 
- You do not need to `npm install @inertiajs/server`
- Come back here when you get to the "Building your application" section

I won't go into detail on how to setup SSR with Vite and Laravel,
but the upcoming [Inertia docs](https://next.inertiajs.com/) have more detailed information
along with the [Laravel docs](https://laravel.com/docs/9.x/vite)

By default the SSR gateway is disabled, you can enable this by setting `SIDECAR_INERTIA_VITE_ENABLED=true` in your `.env`
or by adjusting [the configuration file](#publishing-configuration) to your liking.

## Installation

To require this package, run the following: 

```shell
composer require robertboes/sidecar-inertia-vite
```

This will install Sidecar as well.

## Using the Sidecar Gateway 

This package automatically overwrites the Inertia SSR gateway if the config `sidecar-inertia-vite.ssr_gateway_enabled` is true.

If for some reason the gateway isn't properly overwritten you can do this in your own service provider like so:

```php
namespace App\Providers;

use RobertBoes\SidecarInertiaVite\SidecarGateway;
use Illuminate\Support\ServiceProvider;
use Inertia\Ssr\Gateway;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Use Sidecar to run Inertia SSR.
        $this->app->instance(Gateway::class, new SidecarGateway);
    }
}
```

## Publishing Configuration

You can publish the configuration with the following command:

```shell
php artisan vendor:publish --provider="RobertBoes\SidecarInertiaVite\ServiceProvider" --tag="config"
```

## Configuring Sidecar

If you haven't already, you'll need to configure Sidecar.

Publish the `sidecar.php` configuration file by running 

```shell
php artisan sidecar:install
```

To configure your Sidecar AWS credentials interactively, you can run 

```shell
php artisan sidecar:configure
```

The [official Sidecar docs](https://hammerstone.dev/sidecar/docs/main/configuration) go into much further detail.

Now update your `config/sidecar.php` to include the function shipped with this package.

```php
<?php

return [
    'functions' => [
        \RobertBoes\SidecarInertiaVite\SSRFunction::class
    ],
    
    // ...
];
```

## Updating Your JavaScript

> This only covers Vue3, please follow the Inertia docs for Vue2 or React, and please open any issues.

This package assumes you're using a near-stock `vite.config.js`, which means the paths are defined by the Laravel plugin.

And update your `resources/js/ssr.js` to look something like this. The specifics may vary based on your application. If you're using [Ziggy](https://github.com/tighten/ziggy), you'll want to uncomment the Ziggy stuff. (This is based on the Inertia docs, with slight modifications.)

```js
import {createSSRApp, h} from 'vue'
import {renderToString} from '@vue/server-renderer'
import {createInertiaApp} from '@inertiajs/inertia-vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = 'Laravel';

export async function handler(page) {
    return await createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue', { eager: false })),
        setup({ app, props, plugin }) {
            return createSSRApp({ render: () => h(app, props) })
                .use(plugin)
                // .use(ZiggyVue, {
                //     ...page.props.ziggy,
                //     location: new URL(page.props.ziggy.location),
                // })
        },
    })
}
```

## Bundling / node_modules

By default the config option `sidecar-inertia-vite.bundle` is set to true.
This will bundle your SSR function using [@vercel/ncc](https://github.com/vercel/ncc),
which produces a single file and doesn't require the inclusion of the `node_modules` folder.
Using this approach it will generate a single `index.mjs` file along with dynamically imported modules.

If you wish to not use this you can set the option to `false`. This will create a Lambda function with roughly the following contents:
```
- assets/
- node_modules/
- ssr.mjs
```

## Deploying Your SSR Function

After you have added the SSR function to your `sidecar.php`, you should run `php artisan sidecar:deploy --activate` to
deploy your function. 

This will compile your JavaScript for you as a `beforeDeployment` hook, so you don't have to worry about remembering to do that first.

## Debugging SSR

It's recommended that you deploy your Sidecar function locally so that you can test SSR more quickly. You can run `php artisan sidecar:deploy --activate` from your local machine and your SSR function will be deployed to Lambda.

You can also set `ssr.sidecar.debug` to `true` in your `config/inertia.php` file, so that Sidecar will throw exceptions when SSR fails instead of falling back to client-side rendering. This will help you diagnose issues quickly. 

## A note on Ziggy

I personally don't use Ziggy, but I did try this in a Laravel Jetstream application and everything seemed to work fine.
If anything doesn't work or the docs need more explanation, feel free to submit a PR.
