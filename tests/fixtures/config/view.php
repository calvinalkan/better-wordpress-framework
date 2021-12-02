<?php

declare(strict_types=1);

return [
    
    'paths' => [
        
        VIEWS_DIR,
        BLADE_VIEWS,
    
    ],
    
    'composers' => [
        
        'Tests\View\fixtures\ViewComposers',
    
    ],
    
    'blade_cache' => BLADE_CACHE,

];