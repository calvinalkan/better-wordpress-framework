<?php

declare(strict_types=1);

namespace Tests\integration\better_wp_hooks\fixtures;

use Snicco\EventDispatcher\Contracts\Event;

interface LoggableEvent extends Event
{
    
    public function message();
    
}