<?php

declare(strict_types=1);

use Tests\Core\fixtures\Conditions\TrueCondition;

return [
    
    //'definitions' => ROUTES_DIR,
    
    'api' => [
        'endpoints' => [
            'test' => 'api-prefix/base',
        ],
    ],
    
    'trailing_slash' => false,
    
    'controllers' => [
        'Tests\fixtures\Controllers\Web',
        'Tests\fixtures\Controllers\Admin',
        'Tests\fixtures\Controllers\Ajax',
    ],
    
    'conditions' => [
        'true' => TrueCondition::class,
    ],

];
