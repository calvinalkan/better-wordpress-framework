<?php

declare(strict_types=1);

namespace Tests\integration\blade\fixtures\Components;

use Snicco\Blade\BladeComponent;

class HelloWorld extends BladeComponent
{
    
    public function render()
    {
        return $this->view('components.hello-world');
    }
    
}