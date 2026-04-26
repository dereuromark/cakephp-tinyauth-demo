<?php

return [
    // DebugKit's toolbar emits unnonced inline scripts and styles that
    // violate strict CSP — disabled here while the demo is being run
    // under strict CSP. Re-enable for local non-CSP development.
    //'DebugKit' => [
    //    'onlyDebug' => true,
    //],
    'Bake' => [
        'onlyCli' => true,
        'optional' => true,
    ],
    'Migrations' => [
        'onlyCli' => true,
    ],
    'TinyAuth' => [],
    'TinyAuthBackend' => [],
    'Authorization' => [],
    'Tools' => [],
    'IdeHelper' => [
        'onlyDebug' => true,
        'onlyCli' => true,
        'optional' => true,
    ],
];
