<?php

use RobertBoes\SidecarInertiaVite\SSRFunction;

return [
    // Indicates if this Inertia SSR gateway should be enabled
    // By default it is not, as you might not want to run this locally
    'ssr_gateway_enabled' => env('SIDECAR_INERTIA_VITE_ENABLED', false),

    // The Sidecar function that handles the SSR.
    'handler' => SSRFunction::class,

    // Indicates if NCC should be used to produce a single bundle.
    // If set to false the node_modules folder will be added to the bundle
    'bundle' => true,

    // Log some stats on how long each Lambda request takes.
    'timings' => false,
    
    // Throw exceptions, should they occur.
    'debug' => env('APP_DEBUG', false),
    
    // Compile Ziggy routes with the Lambda function.
    'ziggy' => false,
];
