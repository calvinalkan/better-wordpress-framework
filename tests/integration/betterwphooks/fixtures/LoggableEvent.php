<?php

declare(strict_types=1);

namespace Tests\integration\betterwphooks\fixtures;

use Snicco\EventDispatcher\Contracts\Event;

interface LoggableEvent extends Event
{
    
    public function message();
    
}